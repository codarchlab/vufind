#!/bin/bash
#
# Bash script to start the import of MARC-XML data harvested via OAI-PMH
#
# VUFIND_HOME
#   Path to the vufind installation
#
# usage:
#	update.sh DATE
#
# arguments:
#	DATE in YYYY-MM-DD format sets the from argument for OAI-PMH
#

##################################################
# Set VUFIND_HOME
##################################################
if [[ -z "$VUFIND_HOME" ]]
then
  VUFIND_HOME="/usr/local/vufind"
fi

today=$(date +"%Y-%m-%d")

if [[ -z "$KOHA_BASE_URL" ]]
then
  KOHA_BIBLIO_URL="https://kohadev.dainst.org/download/exports/$today/bibliographic_data.xml"
else
  KOHA_BIBLIO_URL="$KOHA_BASE_URL/$today/bibliographic_data.xml"
fi

echo "Loading updated bibliographic data from $KOHA_BIBLIO_URL:"
wget "$KOHA_BIBLIO_URL" -P "$VUFIND_HOME/local/harvest/dai-katalog/" --no-verbose

if [[ -s "$VUFIND_HOME/local/harvest/dai-katalog/bibliographic_data.xml" ]]
then
    echo "Running VuFind's batch import scripts."
    "$VUFIND_HOME"/harvest/batch-import-marc.sh dai-katalog | tee $VUFIND_HOME/local/harvest/dai-katalog/log/import_$today.log
    echo "Done."
else
    echo "$VUFIND_HOME/local/harvest/dai-katalog/bibliographic_data.xml is an empty file, nothing is getting updated."
    rm $VUFIND_HOME/local/harvest/dai-katalog/bibliographic_data.xml
fi
