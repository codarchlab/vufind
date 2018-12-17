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
if [ -z "$VUFIND_HOME" ]
then
  VUFIND_HOME="/usr/local/vufind"
fi

today=$(date +"%Y-%m-%d")

if [ -z "$KOHA_DOWNLOAD_URL" ]
then
  KOHA_DOWNLOAD_URL="https://kohadev.dainst.org/download/exports/$today/bibliographic_data.xml"
fi

echo "Loading updated bibliographic data from $KOHA_DOWNLOAD_URL."
wget "$KOHA_DOWNLOAD_URL" -P "$VUFIND_HOME/local/harvest/dai-katalog/"

$VUFIND_HOME/harvest/batch-import-marc.sh dai-katalog > $VUFIND_HOME/local/harvest/dai-katalog/log/import_$today.log
$VUFIND_HOME/harvest/batch-delete.sh dai-katalog
