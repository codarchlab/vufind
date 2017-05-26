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
	# echo "converting ${filename}"
	# ruby $VUFIND_HOME/local/import/aleph2marc.rb ${filename}
	ruby $VUFIND_HOME/local/import/preprocess-marc.rb ${filename}
	echo "importing $filename}"
	bash $VUFIND_HOME/import-marc.sh $VUFIND_HOME/local/import/mrc/$filename
done