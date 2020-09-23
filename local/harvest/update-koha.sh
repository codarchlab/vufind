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
  KOHA_BIBLIO_URL="https://koha.dainst.org/download/exports/$today/bibliographic_data.xml"
else
  KOHA_BIBLIO_URL="$KOHA_BASE_URL/$today/bibliographic_data.xml"
fi

echo "Loading updated bibliographic data from $KOHA_BIBLIO_URL:"
wget "$KOHA_BIBLIO_URL" -P "$VUFIND_HOME/local/harvest/dai-katalog/notpreprocessed/" --no-verbose

if [[ -s "$VUFIND_HOME/local/harvest/dai-katalog/notpreprocessed/" ]]
then
    echo "Running VuFind's batch import scripts."
    python3 "$VUFIND_HOME"/preprocess-marc.py "$VUFIND_HOME/local/harvest/dai-katalog/notpreprocessed/" "$VUFIND_HOME/local/harvest/dai-katalog/"
    "$VUFIND_HOME"/harvest/batch-import-marc.sh dai-katalog | tee $VUFIND_HOME/local/harvest/dai-katalog/log/import_$today.log
    echo "Done."
else
    echo "$VUFIND_HOME/local/harvest/dai-katalog/bibliographic_data.xml is an empty file, nothing is getting updated."
fi

rm $VUFIND_HOME/local/harvest/dai-katalog/bibliographic_data.xml
rm $VUFIND_HOME/local/harvest/dai-katalog/notpreprocessed/bibliographic_data.xml

if [[ -z "$KOHA_BASE_URL" ]]
then
  KOHA_AUTH_URL="https://koha.dainst.org/download/exports/$today/authority_data.mrc"
else
  KOHA_AUTH_URL="$KOHA_BASE_URL/$today/authority_data.mrc"
fi

echo "Loading updated authority data from KOHA_AUTH_URL:"

mkdir -p $VUFIND_HOME/local/harvest/dai-katalog-auth/log
wget "$KOHA_AUTH_URL" -P "$VUFIND_HOME/local/harvest/dai-katalog-auth/" --no-verbose

if [[ -s "$VUFIND_HOME/local/harvest/dai-katalog-auth/authority_data.mrc" ]]
then
    echo "Running VuFind's batch import scripts."
    "$VUFIND_HOME"/harvest/batch-import-marc-auth.sh dai-katalog-auth marc_auth.properties | tee $VUFIND_HOME/local/harvest/dai-katalog-auth/log/import_$today.log
    echo "Done."
else
    echo "$VUFIND_HOME/local/harvest/dai-katalog-auth/authority_data.mrc is an empty file, nothing is getting updated."
    rm $VUFIND_HOME/local/harvest/dai-katalog-auth/authority_data.mrc
fi
