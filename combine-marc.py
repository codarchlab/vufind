import pymarc
import logging
import os
import argparse
from datetime import datetime



logger = logging.getLogger(__name__)
logger.setLevel(logging.INFO)
logger.addHandler(logging.StreamHandler())
formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')


MARCXML_OPENING_ELEMENTS = bytes(
    '<?xml version="1.0" encoding="UTF-8" ?><collection xmlns="http://www.loc.gov/MARC21/slim" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.loc.gov/MARC21/slim http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd">',
    'utf-8'
)

MARCXML_CLOSING_ELEMENTS = bytes(
    '</collection>', 'utf-8'
)

parser = argparse.ArgumentParser(description='Preprocess MARCXML data to be imported into Vufind.')
parser.add_argument('input_directory', type=str, help="The directory containing MARCXML files to be combined.")

def run(file_paths, output_directory):
    os.makedirs(output_directory, exist_ok=True)
    with open("{0}/data.xml".format(output_directory), 'wb') as output_file:
        output_file.write(MARCXML_OPENING_ELEMENTS)
        for file_path in file_paths:
            try:
                with open(file_path, 'rb') as input_file:
                    for record in pymarc.parse_xml_to_array(input_file):
                        output_file.write(pymarc.record_to_xml(record))
                    move_to= "{0}/combined/{1}".format(output_directory, os.path.basename(file_path))
                    os.makedirs(os.path.dirname(move_to), exist_ok=True)
                    os.rename(file_path, move_to)
            except Exception as e:
                logger.error(e)
                move_to =  "{0}/error/{1}".format(output_directory, os.path.basename(file_path))
                os.makedirs(os.path.dirname(move_to), exist_ok=True)
                os.rename(file_path, move_to)
                
        output_file.write(MARCXML_CLOSING_ELEMENTS)

if __name__ == '__main__':
    options = vars(parser.parse_args())

    input_directory = options['input_directory']

    try:
        files = [ os.path.join(options['input_directory'], file) for file in os.listdir(options['input_directory']) if os.path.splitext(file)[1] == '.xml' ]
    except NotADirectoryError as e:
        logger.error(e)
    if not files:
        logger.error("Found no xml files at {0}".format(options['input_directory']))
    if files:
        run(files, "{0}/preprocess".format(input_directory))
