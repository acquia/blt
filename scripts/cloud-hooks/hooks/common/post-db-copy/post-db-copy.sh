#!/bin/sh
#
# db-copy Cloud hook: post-db-copy
#
# Run updates on copied databases.
#
# Usage: post-db-copy.sh site target-env db-name source-env

set -ev

site="$1"
target_env="$2"
db_name="$3"
source_env="$4"

# Prep for BLT commands.
repo_root="/var/www/html/$site.$target_env"
export PATH=$repo_root/vendor/bin:$PATH
cd $repo_root

blt artifact:ac-hooks:post-db-copy $site $target_env $db_name $source_env --environment=$target_env -v --yes --no-interaction

set +v
