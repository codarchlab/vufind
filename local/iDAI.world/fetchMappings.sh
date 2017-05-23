#!/usr/bin/env bash

##################################################
# Set VUFIND_HOME
##################################################
if [ -z "$VUFIND_HOME" ]
then
  VUFIND_HOME="/usr/local/vufind"
fi

curl -H "Accept: application/json" "https://publications.dainst.org/journals/plugins/pubIds/zenon/api.php" > $VUFIND_HOME/local/iDAI.world/publications_mapping.json