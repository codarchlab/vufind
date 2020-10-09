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

mkdir -p $VUFIND_HOME/local/harvest/dai-katalog/log

php $VUFIND_HOME/harvest/harvest_oai.php dai-katalog --from $1 2>&1 | tee $VUFIND_HOME/local/harvest/dai-katalog/log/harvest_$today.log
python3 $VUFIND_HOME/combine-marc.py $VUFIND_HOME/local/harvest/dai-katalog 2>&1 | tee $VUFIND_HOME/local/harvest/dai-katalog/log/combine_$today.log
python3 "$VUFIND_HOME"/preprocess-marc.py $VUFIND_HOME/local/harvest/dai-katalog/preprocess $VUFIND_HOME/local/harvest/dai-katalog --url "http://zenon.dainst.org" 2>&1 | tee $VUFIND_HOME/local/harvest/dai-katalog/log/preprocess_$today.log # "http://$(hostname -i)"
$VUFIND_HOME/harvest/batch-import-marc.sh dai-katalog 2>&1 | tee $VUFIND_HOME/local/harvest/dai-katalog/log/import_$today.log
$VUFIND_HOME/harvest/batch-delete.sh dai-katalog 2>&1 | tee $VUFIND_HOME/local/harvest/dai-katalog/log/delete_$today.log
