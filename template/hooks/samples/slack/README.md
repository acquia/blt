# Slack Notification

Author: Grant Gaudet

### Purpose

This cloud hook posts a notification to Slack chat room after a code deployment
has been performed on Acquia Cloud.

### Example Scenario

1. A new tag is deployed to the production environment.
2. A slack notification is posted indicating that a tag has been deployed.

### Installation Steps

Installation Steps (assumes Slack subscription setup and Acquia Cloud Hooks installed in repo):

* See the API documentation at https://api.slack.com/ get your `TOKEN`.
* Store this variable in `$HOME/slack_settings` file on your Acquia Cloud Server (see slack_settings file).
* Set the execution bit to on e.g. `chmod a+x slack_settings`
* Add `slack.sh` to dev, test, prod or common __post-cody-deploy__ hook.


