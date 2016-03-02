#!/usr/bin/env bash

ROOTPATH="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

echo "Removing demo sites..."
DIRS=`find "$ROOTPATH/site" -maxdepth 1 -mindepth 1 -type d -cmin +120 -exec basename {} \;`
for DIR in $DIRS
do
    if [[ -d "$ROOTPATH/site/$DIR" ]]; then
        echo "Removing site/$DIR..."
        rm -rf "$ROOTPATH/site/$DIR"
    fi
done

echo "Finished"