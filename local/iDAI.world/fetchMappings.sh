#!/usr/bin/env bash

##################################################
# Set VUFIND_HOME
##################################################
if [ -z "$VUFIND_HOME" ]
then
  VUFIND_HOME="/usr/local/vufind"
fi

exec &> $VUFIND_HOME/local/iDAI.world/log/publications_`date +\%Y-\%m-\%d`.log
curl -H "Accept: application/json" "https://publications.dainst.org/journals/plugins/pubIds/zenon/api.php" > $VUFIND_HOME/local/iDAI.world/publications_serials_mapping.json
curl -H "Accept: application/json" "https://publications.dainst.org/books/plugins/generic/ojs-cilantro-plugin/api/zenon" > $VUFIND_HOME/local/iDAI.world/publications_books_mapping.json