#!/bin/bash
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

set -ev

site="$1"
target_env="$2"
source_branch="$3"
deployed_tag="$4"
repo_url="$5"
repo_type="$6"


  . /var/www/html/$site.$target_env/vendor/acquia/blt/scripts/cloud-hooks/functions.sh
  deploy_updates
  # Send notifications to Slack, if configured. See readme/deploy.md for setup instructions.
  . `dirname $0`/../slack.sh

set +v
