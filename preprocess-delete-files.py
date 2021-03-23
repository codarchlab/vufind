import argparse
import logging
import os
import urllib
import urllib.request
import re
import json

logger = logging.getLogger(__name__)
logger.setLevel(logging.INFO)
formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')
sh = logging.StreamHandler()
sh.setFormatter(formatter)
logger.addHandler(sh)

parser = argparse.ArgumentParser(description='Preprocess MARCXML data to be imported into Vufind.')
parser.add_argument('input_directory', type=str, help="Input directory with harvested delete files.")
parser.add_argument('--url', dest='server_url', type=str, default="https://zenon.dainst.org", help="Optional server URL where to check for Zenon IDs.")

# Koha uses its internal biblionumber (999$c) as the ID in OAI, but in VuFind we use the systemnumber (001) as the primary id. This script rewrites the 
# harvested delete files to their systemnumber equivalents.

def run(input_files):
    global invalid_zenon_ids

    logger.info("Preprocessing files.")
    for file_path in input_files:
        directory = os.path.dirname(file_path)
        file_name = os.path.basename(file_path)

        prefix = file_name.split("_")[0]

        # logger.info(file_path)
        # logger.info(directory)
        # logger.info(file_name)
        # logger.info(prefix)

        with open(file_path, 'r') as input_file:
            biblio_number = input_file.readline()

            url = "{0}/api/v1/search?lookfor=biblio_no:{1}&type=AllFields".format(server_url, biblio_number)

            req = urllib.request.Request(url)

            try:
                with urllib.request.urlopen(req) as response:
                    result = json.loads(response.read())
                    if "records" in result:
                        zenon_id = result["records"][0]["id"]
                        output_path = "{0}/{1}_{2}.delete".format(directory, prefix, zenon_id)
                        with open(output_path, 'w') as output_file:
                            output_file.write(zenon_id)
                    else:
                        logger.error("No biblio number {0} found. Unable to delete.".format(biblio_number))
            except Exception as e:
                logger.error(e)

        os.remove(file_path)

if __name__ == '__main__':
    global server_url
    global check_biblio_no

    options = vars(parser.parse_args())

    server_url = options['server_url']

    files = [ os.path.join(options['input_directory'], file) for file in os.listdir(options['input_directory']) if os.path.splitext(file)[1] == '.delete' ]

    if not files:
        logger.info("Found no delete files at {0}".format(options['input_directory']))
    if files:
        run(files)
