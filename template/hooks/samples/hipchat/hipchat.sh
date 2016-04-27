#!/bin/sh
#
# Cloud Hook: post-code-deploy
#
# The post-code-deploy hook is run whenever you use the Workflow page to
# deploy new code to an environment, either via drag-drop or by selecting
# an existing branch or tag from the Code drop-down list. See
# ../README.md for details.
#
# Usage: post-code-deploy site target-env source-branch deployed-tag repo-url
#                         repo-type

site="$1"
target_env="$2"
source_branch="$3"
deployed_tag="$4"
repo_url="$5"
repo_type="$6"

# Load the HipChat webhook URL (which is not stored in this repo).
. $HOME/hipchat_settings

# Post deployment notice to HipChat

if [ "$source_branch" != "$deployed_tag" ]; then
  curl --header "content-type: application/json" --header "Authorization: Bearer $AUTH_TOKEN" -X POST \
    -d "{\"message\":\"An updated deployment has been made to $site.$target_env using branch $source_branch as $deployed_tag.\"}" $HIPCHAT_WEBHOOK_URL
else
  curl --header "content-type: application/json" --header "Authorization: Bearer $AUTH_TOKEN" -X POST \
    -d "{\"message\":\"An updated deployment has been made to $site.$target_env using tag $deployed_tag.\"}" $HIPCHAT_WEBHOOK_URL
fi


