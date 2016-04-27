# New Relic Deployment Notification

Author: Erik Webb

### Purpose

This will post a message to New Relic's API when a new code deployment has been made
to an Acquia environment. This allows New Relic to plot releases onto performance
graphs.

### Example Scenario

1. A new tag is deployed to the production environment.
2. A POST request is sent to New Relic indicating when the given tag was deployed.
3. Deployment information is available on New Relic's graphs and charts.

### Installation Steps (assumes New Relic subscription setup and Acquia Cloud Hooks installed in repo)

* Login to New Relic and goto https://rpm.newrelic.com/accounts/(UserID)/applications/(ApplicationID)/deployments/instructions
* From the instructions get your `application_id` and your `x-api-key`. Store these variables and a username you wish to send to New Relic in `$HOME/newrelic_settings` file on your Acquia Cloud Server (see example file).
* Set the execution bit to on i.e. `chmod a+x newrelic_settings`
* Add `newrelic.sh` to dev, test, prod or common __post-cody-deploy__ hook.


