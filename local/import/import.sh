#!/bin/bash
#
# Bash script to start the import of a aleph sequential file for Solr indexing.
#
# VUFIND_HOME
#   Path to the vufind installation
#

for filename in $1*.gz 
do 
	echo "converting ${filename}"
	ruby $VUFIND_HOME/local/import/aleph2marc.rb ${filename}
	echo "importing ${filename}"
	bash $VUFIND_HOME/import-marc.rb
done