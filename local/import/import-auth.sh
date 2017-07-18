#!/bin/bash
#
# Bash script to start the import of a aleph sequential file for Solr auth indexing.
#
# VUFIND_HOME
#   Path to the vufind installation
#

##################################################
# Set VUFIND_HOME
##################################################
if [ -z "$VUFIND_HOME" ]
then
  VUFIND_HOME="/usr/local/vufind"
fi

for filename in $1*
do
	echo "converting ${filename}"
	ruby $VUFIND_HOME/local/import/aleph2marc.rb ${filename}
    echo "importing ${filename}"
	bash $VUFIND_HOME/import-marc-auth.sh $VUFIND_HOME/local/import/mrc/$(basename $filename) marc_auth_zenon_ths.properties
done
