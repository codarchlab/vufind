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

if [ -z "$VUFIND_LOCAL_DIR" ]
then
  VUFIND_LOCAL_DIR="$VUFIND_HOME/local"
fi

today=$(date +"%Y-%m-%d")

mkdir -p $VUFIND_HOME/local/harvest/dai-katalog/log

php $VUFIND_HOME/harvest/harvest_oai.php dai-katalog --from $1 2>&1 | tee $VUFIND_HOME/local/harvest/dai-katalog/log/harvest_$today.log
python3 $VUFIND_HOME/combine-marc.py $VUFIND_HOME/local/harvest/dai-katalog 2>&1 | tee $VUFIND_HOME/local/harvest/dai-katalog/log/combine_$today.log
python3 "$VUFIND_HOME"/preprocess-marc.py $VUFIND_HOME/local/harvest/dai-katalog/preprocess $VUFIND_HOME/local/harvest/dai-katalog --url "http://$(hostname -i)" --check_biblio 2>&1 | tee $VUFIND_HOME/local/harvest/dai-katalog/log/preprocess_$today.log # "http://$(hostname -i)"
$VUFIND_HOME/harvest/batch-import-marc.sh dai-katalog 2>&1 | tee $VUFIND_HOME/local/harvest/dai-katalog/log/import_$today.log

$VUFIND_HOME/harvest/batch-delete.sh dai-katalog 2>&1 | tee $VUFIND_HOME/local/harvest/dai-katalog/log/delete_$today.log

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

python3 $VUFIND_HOME/preprocess-delete-files.py $VUFIND_HOME/local/harvest/dai-katalog | tee $VUFIND_HOME/local/harvest/dai-katalog/log/delete_$today.log
$VUFIND_HOME/harvest/batch-delete.sh dai-katalog 2>&1 | tee -a $VUFIND_HOME/local/harvest/dai-katalog/log/delete_$today.log