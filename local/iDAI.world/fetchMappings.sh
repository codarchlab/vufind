#!/usr/bin/env bash

##################################################
# Set VUFIND_HOME
##################################################
if [ -z "$VUFIND_HOME" ]
then
  VUFIND_HOME="/usr/local/vufind"
fi

echo "Fetching mapping from publications."
wget "https://publications.dainst.org/journals/plugins/pubIds/zenon/api.php" -P "$VUFIND_HOME/local/iDAI.world/publications_mapping.json" --no-verbose
echo "Done."
