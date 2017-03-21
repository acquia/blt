#!/bin/sh

FILE=$HOME/slack_settings

if [ -f $FILE ]; then
  # Load the Slack webhook URL (which is not stored in this repo).
  . $HOME/slack_settings

  if [ $status -ne 0 ]; then
    # Failed deploy.
    curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \"Deployment has FAILED for environment *$site.$target_env*.\", \"icon_emoji\": \":rain_cloud:\"}" $SLACK_WEBHOOK_URL
  else
    # Successful deploy.
    if [ "$source_branch" != "$deployed_tag" ]; then
      curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \"An updated deployment has been made to *$site.$target_env* using branch *$source_branch* as *$deployed_tag*.\", \"icon_emoji\": \":mostly_sunny:\"}" $SLACK_WEBHOOK_URL
    else
      curl -X POST --data-urlencode "payload={\"username\": \"Acquia Cloud\", \"text\": \"An updated deployment has been made to *$site.$target_env* using tag *$deployed_tag*.\", \"icon_emoji\": \":mostly_sunny:\"}" $SLACK_WEBHOOK_URL
    fi
  fi
else
  echo "Notice: Slack notifications disabled: file $FILE does not exist."
fi
