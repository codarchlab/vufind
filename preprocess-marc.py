import pymarc
import argparse
import logging
import os
import urllib
import re

logger = logging.getLogger(__name__)
logger.setLevel(logging.DEBUG)
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
            invalid_zenon_ids += [(sys_number, parent_sys_number)]

            number_match = re.fullmatch(contains_only_numbers, parent_sys_number)
            if number_match:
                if(len(parent_sys_number) > 9):
                    parent['w'] = parent_sys_number[(len(parent_sys_number)-9):]
                else:
                    pad = '0' * (9 - len(parent_sys_number))
                    parent['w'] = pad + parent_sys_number
                parent_ids += [parent['w']]
        else:
            parent_ids += [parent['w']]

    return list(set(parent_ids))


def extract_holding_branch_codes(holding_fields):
    holding_branches = []
    for holding in holding_fields:
        holding_branches.append(holding['b'])
    return holding_branches

def accumulate_ancestor_holdings(ids, current_depths = 0):

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
                logger.error("{1}, https://zenon.dainst.org/Record/{0}".format(id, e))
            except Exception as e:
                logger.error("{1}, https://zenon.dainst.org/Record/{0}".format(id, e))

    if parent_ids:
        return list(set(holding_branches + accumulate_ancestor_holdings(parent_ids, current_depths=current_depths+1)))
    return parent_ids

def run(options):
    global invalid_zenon_ids
    with open(options['input_file'], 'rb') as input_file, open("{0}/{1}".format(options['output_directory'], os.path.basename(options['input_file'])), 'wb') as output_file:
        reader = pymarc.parse_xml_to_array(input_file)
        
        output_file.write(MARCXML_OPENING_ELEMENTS)

        for record in reader:
            sys_number = record['001'].data
            
            holdings = record.get_fields('952')
            holding_branches = extract_holding_branch_codes(holdings)

            parent_ids = []
            parents = record.get_fields('773')
            parent_ids = extract_parent_ids(sys_number, parents)
            
            holdings_mapping[sys_number] = (parent_ids, holding_branches)

            ancestor_holding_branches = accumulate_ancestor_holdings(parent_ids)
            ancestor_holding_branches = [x for x in ancestor_holding_branches if x not in holding_branches]
            
            if ancestor_holding_branches:
                logger.info("sysnumber: {0}".format(sys_number))
                logger.info("holding branches: {0}".format(holding_branches))
                logger.info("ancestor holding branches: {0}".format(ancestor_holding_branches))

                for branch in ancestor_holding_branches:
                    record.add_field(
                        pymarc.Field(
                            '953',
                            indicators=[' ', ' '],
                            subfields=[ 'b', branch, 'z', "Automatically added holding branch key." ]
                        )
                    )
            output_file.write(pymarc.record_to_xml(record))

        output_file.write(MARCXML_CLOSING_ELEMENTS)

        logger.warning("Encountered {0} invalid zenon IDs:".format(len(invalid_zenon_ids)))
        invalid_zenon_ids = list(set(invalid_zenon_ids))
        for entry in invalid_zenon_ids:
            logger.warning("{0} contained {1} as parent.".format(entry[0], entry[1]))

if __name__ == '__main__':
    options = vars(parser.parse_args())

    run(options)