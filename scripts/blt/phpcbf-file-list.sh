#!/usr/bin/env bash

GIT_ROOT=$( git rev-parse --show-toplevel )
PHPCBF_BIN=${GIT_ROOT}/vendor/bin/phpcbf
FILENAME=$1

if [ ! -f $PHPCBF_BIN ];
  then
    echo "PHP Code Beautifier was not found in this project's bin directory. Please run composer install."
    exit 1
fi

if [ -z $FILENAME ];
  then
    echo "Missing file list parameter."
    exit 1
fi

# Get list of files to fix from input csv file.
LIST=$( cat $FILENAME | awk -F '^\"|\",\"*' '{print $2}' | sort -u )

# Replace report file contents with contents of $LIST.
> $FILENAME
if [ -z "$LIST" ];
  then
    echo "No fixable files found."
    exit
  else
    for i in $LIST; do
      echo  $i >> $FILENAME
    done
    echo "Files that can be fixed by PHPCBF:"
    cat $FILENAME
fi

exit
