#!/usr/bin/env bash

##################################################
# Set VUFIND_HOME
##################################################
if [ -z "$VUFIND_HOME" ]
then
  VUFIND_HOME="/usr/local/vufind"
fi

curl -H "Accept: application/json" -s --show-error -o $VUFIND_HOME/local/iDAI.world/publications_serials_mapping_tmp.json --fail "https://publications.dainst.org/journals/plugins/pubIds/zenon/api/index.php?task=mapping"
if [ 0 -eq $? ]; 
then
  echo "Updating iDAI.publication's journals mapping successful."
  mv $VUFIND_HOME/local/iDAI.world/publications_serials_mapping_tmp.json $VUFIND_HOME/local/iDAI.world/publications_serials_mapping.json
else
  echo "Error updating  iDAI.publication's journals mapping, keeping existing mapping."
  rm -f $VUFIND_HOME/local/iDAI.world/publications_serials_mapping_tmp.json
fi;

curl -H "Accept: application/json" -s -o $VUFIND_HOME/local/iDAI.world/publications_books_mapping_tmp.json --fail "https://publications.dainst.org/books/plugins/pubIds/zenon/api/index.php?task=mapping"
if [ 0 -eq $? ]; 
then
  echo "Updating iDAI.publication's books mapping successful."
  mv $VUFIND_HOME/local/iDAI.world/publications_books_mapping_tmp.json $VUFIND_HOME/local/iDAI.world/publications_books_mapping.json
else
  echo "Error updating iDAI.publication's books mapping, keeping existing mapping."
  rm -f $VUFIND_HOME/local/iDAI.world/publications_books_mapping_tmp.json
fi;
