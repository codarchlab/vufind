#!/usr/bin/env bash

##################################################
# Set VUFIND_HOME
##################################################
if [ -z "$VUFIND_HOME" ]
then
  VUFIND_HOME="/usr/local/vufind"
fi

PUBLICATIONS_LOG_DIRECTORY="$VUFIND_HOME/local/iDAI.world/log"

if [[ ! -d "$PUBLICATIONS_LOG_DIRECTORY" ]]; then
  mkdir "$PUBLICATIONS_LOG_DIRECTORY"
fi

exec &> "$PUBLICATIONS_LOG_DIRECTORY/publications_`date +\%Y-\%m-\%d`.log"

curl -H "Accept: application/json" "https://publications.dainst.org/journals/plugins/pubIds/zenon/api/mapping" > $VUFIND_HOME/local/iDAI.world/publications_serials_mapping.json
curl -H "Accept: application/json" "https://publications.dainst.org/books/plugins/pubIds/zenon/api/mapping" > $VUFIND_HOME/local/iDAI.world/publications_books_mapping.json

exit
