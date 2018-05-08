#!/bin/bash
#
# Bash script to start the import of MARC-XML thesaurus data harvested via OAI-PMH
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


# TODO: Update for Koha
# php $VUFIND_HOME/harvest/harvest_oai.php dai-ths --from $1
# $VUFIND_HOME/harvest/batch-import-marc.sh dai-ths $VUFIND_HOME/import/marc_auth_zenon_ths.properties
# $VUFIND_HOME/harvest/batch-delete.sh dai-ths
