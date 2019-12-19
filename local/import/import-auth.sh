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
    echo "importing ${filename}"
    bash $VUFIND_HOME/import-marc-auth.sh $filename
done
