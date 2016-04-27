#!/bin/bash
#
# db-copy Cloud hook: db-scrub-s3
#
# 1. Scrub important information from a Drupal database on copy into dev
# 2. Backup file to user home directory
# 3. Upload sanitized db to Amazon s3 bucket id 
#
# Usage: db-scrub-s3.sh site target-env db-name source-env db-name source_env

site="$1"
target_env="$2"
db_name="$3"
source_env="$4"
file=~/upload.sql
bucket=psotest

#env check
if [ $2 == "dev" ]; then  

  echo "$site.$target_env: Scrubbing database $db_name"
  drush @$site.$target_env sql-sanitize -y

  drush @$site.$target_env sql-dump > $file
  gzip -qf  ~/upload.sql 

  file=~/upload.sql.gz

  echo "uploading scrubbed database $file.tgz to s3"

  s3Key=XXXXXXXXXXXXXXXXXXXXXX
  s3Secret=XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
  file=~/upload.sql.gz
  bucket=psotest
  resource="/${bucket}/${file}"
  contentType="application/gzip"
  dateValue=`date -u  +"%a, %d %b %Y %k:%M:%S +0000"`
  stringToSign="PUT\n\n${contentType}\n${dateValue}\n${resource}"
  signature=`echo -en ${stringToSign} | openssl sha1 -hmac ${s3Secret} -binary | base64`
  curl -X PUT -T "${file}" \
    -H "Host: ${bucket}.s3.amazonaws.com" \
    -H "Date: ${dateValue}" \
    -H "Content-Type: ${contentType}" \
    -H "Authorization: AWS ${s3Key}:${signature}" \
    https://${bucket}.s3.amazonaws.com/${file}

fi

