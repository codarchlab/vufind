#!/bin/bash
#
# Bash script to start the import of a aleph sequential file for Solr indexing.
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

for filename in $1* #.gz
do 
	echo "importing ${filename}"
	bash $VUFIND_HOME/import-marc.sh $VUFIND_HOME/local/import/mrc/$(basename $filename)
done