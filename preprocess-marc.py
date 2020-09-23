import pymarc
import argparse
import logging
import os
import urllib
import re

logger = logging.getLogger(__name__)
logger.setLevel(logging.INFO)
formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')

def is_writable_directory(path: str):
    if os.path.exists(path) and (not os.path.isdir(path) or not os.access(path, os.W_OK)):
        msg = "Please provide writable directory."
        raise argparse.ArgumentTypeError(msg)
    elif not os.path.exists(path):
        os.makedirs(path)
        return path
    else:
        return path

MARCXML_OPENING_ELEMENTS = bytes(
    '<?xml version="1.0" encoding="UTF-8" ?><collection xmlns="http://www.loc.gov/MARC21/slim" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.loc.gov/MARC21/slim http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd">',
    'utf-8'
)

MARCXML_CLOSING_ELEMENTS = bytes(
    '</collection>', 'utf-8'
)

parser = argparse.ArgumentParser(description='Preprocess MARCXML data to be imported into Vufind.')
parser.add_argument('input_file', type=str, help="The MARCXML file to be processed.")
parser.add_argument('output_directory', type=is_writable_directory, help="Output directory for the updated MARC file.")

holdings_mapping = {}
invalid_zenon_ids = []

valid_zenon_id = re.compile(r"\d{9}")
contains_only_numbers = re.compile(r"\d+")

def extract_parent_ids(sys_number, parents):
    global invalid_zenon_ids
    parent_ids = []
    for parent in parents:
        if 'w' not in parent:
            continue
        parent_sys_number = parent['w']
        matcher = re.fullmatch(valid_zenon_id, parent_sys_number)
        if not matcher:

            fixed_value = None

            number_match = re.fullmatch(contains_only_numbers, parent_sys_number)
            if number_match:
                if(len(parent_sys_number) > 9):
                    parent['w'] = parent_sys_number[(len(parent_sys_number)-9):]
                else:
                    pad = '0' * (9 - len(parent_sys_number))
                    parent['w'] = pad + parent_sys_number
                parent_ids += [parent['w']]

                fixed_value = parent['w']

            invalid_zenon_ids += [(sys_number, parent_sys_number, fixed_value)]

        else:
            parent_ids += [parent['w']]

    return list(set(parent_ids))


def extract_holding_branch_codes(holding_fields):
    holding_branches = []
    for holding in holding_fields:
        holding_branches.append(holding['b'])
    return holding_branches

def accumulate_ancestor_holdings(sys_number_first, ids, current_depths = 0):

    if current_depths > 10:
        logger.error("Unusually deeply nested hierarchy for {0}. Aborting recursion.".format(ids))
        return []

    parent_ids = []
    for id in ids:
        if id in holdings_mapping:
            (parent_ids, holding_branches) = holdings_mapping[id]
        else:
            url = "https://zenon.dainst.org/Record/{0}/Export?style=MARCXML".format(id)
            req = urllib.request.Request(url)
            try:
                with urllib.request.urlopen(req) as response:
                    record = pymarc.parse_xml_to_array(response)[0]
                    holdings = record.get_fields('952')
                    holding_branches = extract_holding_branch_codes(holdings)
                    parents = record.get_fields('773')
                    parent_ids = extract_parent_ids(id, parents)

                    holdings_mapping[id] = (parent_ids, holding_branches)
            except urllib.error.HTTPError as e:
                logger.error("{1}, https://zenon.dainst.org/Record/{0}, initial record: https://zenon.dainst.org/Record/{2}.".format(id, e, sys_number_first))
            except Exception as e:
                logger.error("{1}, https://zenon.dainst.org/Record/{0}, initial record: https://zenon.dainst.org/Record/{2}.".format(id, e, sys_number_first))

    if parent_ids:
        return list(set(holding_branches + accumulate_ancestor_holdings(sys_number_first, parent_ids, current_depths=current_depths+1)))
    return parent_ids


def add_to_holding_mapping(record):
    sys_number = record['001'].data
            
    holdings = record.get_fields('952')
    holding_branches = extract_holding_branch_codes(holdings)

    parent_ids = []
    parents = record.get_fields('773')
    parent_ids = extract_parent_ids(sys_number, parents)
    
    holdings_mapping[sys_number] = (parent_ids, holding_branches)

def preprocess_record(record):
    sys_number = record['001'].data

    matcher = re.fullmatch(valid_zenon_id, sys_number)
    if not matcher:
        logger.error("Unusual zenon ID in biblio #{0}. Returning None record.".format(record['999']['c']))
        return None

    (parent_ids, holding_branches) = holdings_mapping[sys_number]
    ancestor_holding_branches = accumulate_ancestor_holdings(sys_number, parent_ids)
    ancestor_holding_branches = [x for x in ancestor_holding_branches if x not in holding_branches]

    holdings = record.get_fields('952')
    internal_subfields = ['d', 'e', 'f', 'g', 'w', 'x', 'A', 'C', 'P', 'T', 'U']
    for holding in holdings:
	    holding.delete_subfield(internal_subfields)

    if ancestor_holding_branches:
        for branch in ancestor_holding_branches:
            record.add_field(
                pymarc.Field(
                    '953',
                    indicators=[' ', ' '],
                    subfields=[ 'b', branch, 'z', "Automatically added holding branch key." ]
                )
            )
    
    return record

def run(file_paths, output_directory):
    global invalid_zenon_ids

    for file_path in file_paths:
        with open(file_path, 'rb') as input_file:
            reader = pymarc.parse_xml_to_array(input_file)

            for record in reader:
                add_to_holding_mapping(record)
    
    for file_path in file_paths:
        with open(file_path, 'rb') as input_file, open("{0}/{1}".format(output_directory, os.path.basename(file_path)), 'wb') as output_file:
            reader = pymarc.parse_xml_to_array(input_file)
            
            output_file.write(MARCXML_OPENING_ELEMENTS)

            for record in reader:
                record = preprocess_record(record)
                if record is None:
                    logger.error("Received None record after processing, skipping.")
                else:
                    output_file.write(pymarc.record_to_xml(record))

            output_file.write(MARCXML_CLOSING_ELEMENTS)

    logger.warning("Encountered {0} invalid zenon IDs:".format(len(invalid_zenon_ids)))
    invalid_zenon_ids = list(set(invalid_zenon_ids))
    for entry in invalid_zenon_ids:
        logger.warning("{0} contained {1} as parent, fixed: {2}.".format(entry[0], entry[1], entry[2]))

if __name__ == '__main__':
    options = vars(parser.parse_args())

    try:
        files = [ os.path.join(options['input_file'], file) for file in os.listdir(options['input_file']) if os.path.splitext(file)[1] == '.xml' ]
    except NotADirectoryError:
        files = [options['input_file']]
    if not files:
        logger.error("Found no xml files at {0}".format(options['input_file']))
    if files:
        run(files, options['output_directory'])