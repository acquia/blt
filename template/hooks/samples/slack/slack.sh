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

# Load the Slack webhook URL (which is not stored in this repo).
. $HOME/slack_settings

# Post deployment notice to Slack

if [ "$source_branch" != "$deployed_tag" ]; then
  curl -X POST --data-urlencode "payload={\"channel\": \"#your_channel\", \"username\": \"Acquia Cloud\", \"text\": \"An updated deployment has been made to *$site.$target_env* using branch *$source_branch* as *$deployed_tag*.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL
else
  curl -X POST --data-urlencode "payload={\"channel\": \"#your_channel\", \"username\": \"Acquia Cloud\", \"text\": \"An updated deployment has been made to *$site.$target_env* using tag *$deployed_tag*.\", \"icon_emoji\": \":acquiacloud:\"}" $SLACK_WEBHOOK_URL
fi
