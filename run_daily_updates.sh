#!/usr/bin/env bash

if [ -z "$VUFIND_HOME" ]
then
  VUFIND_HOME="/usr/local/vufind"
fi

if [ -z "$MACHINE_NAME" ]
then
    MACHINE_NAME="DevKoha"
fi

if [ -z "$KOHA_BASE_URL" ]
then
    KOHA_BASE_URL="https://kohadev.dainst.org/download/exports"
fi

if [ -z "$VUFIND_LOCAL_DIR" ]
then
    VUFIND_LOCAL_DIR=/usr/local/vufind/local
fi

MARC_UPDATE_LOG="$VUFIND_HOME/local/harvest/log/`date +\%Y-\%m-\%d`.log"
"$VUFIND_HOME/local/harvest/update-koha.sh" &> "$MARC_UPDATE_LOG"

RECIPIENT=zenondai@dainst.org

if [[ -z ${MACHINE_NAME:+x} ]] ;
then
    MACHINE_NAME="Unnamed machine"
fi

if grep --ignore-case -q error "$MARC_UPDATE_LOG";
then
    cat "$MARC_UPDATE_LOG" | mail -s "VuFind ($MACHINE_NAME) marc update -- ERROR" -a "From: vufindmailer@dainst.de" "$RECIPIENT"
else
    cat "$MARC_UPDATE_LOG" | mail -s "VuFind ($MACHINE_NAME) marc update -- SUCCESS" -a "From: vufindmailer@dainst.de" "$RECIPIENT"
fi


PUBLICATIONS_UPDATE_LOG="$VUFIND_HOME/local/iDAI.world/log/publications_`date +\%Y-\%m-\%d`.log"

"$VUFIND_HOME/local/iDAI.world/fetchMappings.sh" &> "$PUBLICATIONS_UPDATE_LOG"

if grep --ignore-case -q error "$PUBLICATIONS_UPDATE_LOG";
then
    cat "$PUBLICATIONS_UPDATE_LOG" | mail -s "VuFind ($MACHINE_NAME) publications mapping update -- ERROR" -a "From: vufindmailer@dainst.de" "$RECIPIENT"
else
    cat "$PUBLICATIONS_UPDATE_LOG" | mail -s "VuFind ($MACHINE_NAME) publications mapping update -- SUCCESS" -a "From: vufindmailer@dainst.de" "$RECIPIENT"
fi
