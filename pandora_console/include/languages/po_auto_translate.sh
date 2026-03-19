#!/bin/bash

FILE=$1

SOURCE_LANG="en"
TARGET_LANG=$2

ID=""
STR=""
REAL_ID=""
NEW_STR=""

READING_ID=0
READING_STR=0

TARGET_LANG=$(echo "$TARGET_LANG" | tr '[:upper:]' '[:lower:]')

function help() {
    echo "Usage: $0 <path_to_file.po> <es|ja|ca|fr|ru>"
}

function check_target_lang() {
    case $TARGET_LANG in
        es|ja|ca|fr|ru)
            return 0  # Valid
            ;;
        *)
            return 1  # Invalid
            ;;
    esac
}

function translate_text() {
    local text="$1"

    # URL encode the text
    local encoded_text=$(echo -n "$text" | jq -sRr @uri)

    # Google Translate API endpoint
    local url="https://translate.googleapis.com/translate_a/single?client=gtx&sl=$SOURCE_LANG&tl=$TARGET_LANG&dt=t&q=$encoded_text"

    # Send GET request to Google Translate API
    local translated_text=$(curl -s "$url" | jq -r '.[0][][0]' | tr -d '\n')

    echo "$translated_text"
}

if command -v jq &> /dev/null; then
    if check_target_lang && [ "$FILE" != "" ] && [ -f $FILE ]; then

        while IFS= read -r line; do

            if [[ $line == "#"* ]]; then
                continue
            fi

            if [ "$line" == "" ]; then
                continue
            fi

            if [[ $line == msgid* ]]; then

                # Check if ID is not empty (i.e., if this is not the first ID)
                if [ -n "$ID" ]; then

                    if [ "$ID" != "" ]; then
                        if [ "$STR" == "" ]; then
                            STR=$(translate_text "$ID")
                        fi

                        echo "msgid ${REAL_ID}"$'\n'"msgstr \"${STR}\""$'\n'
                        REAL_ID=""
                        ID=""
                        STR=""
                    fi

                fi

                READING_ID=1
                READING_STR=0
                # Initialize ID with the content after "msgid "
                TMP="${line#msgid }"
                REAL_ID="${TMP}"
                TMP="${TMP#\"}"
                ID="${TMP%\"}"

            elif [[ $line == msgstr* ]]; then

                READING_ID=0
                READING_STR=1
                # Initialize STR with the content after "msgstr "
                TMP="${line#msgstr }"
                TMP="${TMP#\"}"
                STR="${TMP%\"}"

            else

                # If reading ID, append to ID
                if [ $READING_ID -eq 1 ]; then
                    REAL_ID="${REAL_ID}"$'\n'"${line}"
                    TMP="${line#\"}"
                    TMP="${TMP%\"}"
                    ID="$ID$TMP"
                # If reading STR, append to STR
                elif [ $READING_STR -eq 1 ]; then
                    TMP="${line#\"}"
                    TMP="${TMP%\"}"
                    STR="$STR$TMP"
                fi

            fi

        done < $FILE

        if [ "$ID" != "" ]; then
            if [ "$STR" == "" ]; then
                STR=$(translate_text "$ID")
            fi

            echo "msgid ${REAL_ID}"$'\n'"msgstr \"${STR}\""$'\n'
            REAL_ID=""
            ID=""
            STR=""
        fi

    else

        help
        echo
        echo "[ERROR] File [$FILE] not found or not valid language [$TARGET_LANG]"

    fi
else

    echo "[ERROR] Command needed to run script: jq"

fi