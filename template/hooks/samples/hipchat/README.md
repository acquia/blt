# HipChat Notification

Author: Grant Gaudet

### Purpose

This cloud hook posts a notification to a HipChat room after a code deployment
has been performed on Acquia Cloud.

### Example Scenario

1. A new tag is deployed to the production environment.
2. A HipChat notification is posted indicating that a tag has been deployed.

### Installation Steps

Installation Steps (assumes HipChat subscription setup and Acquia Cloud Hooks installed in repo):

* See the API documentation at https://www.hipchat.com/docs/apiv2 and https://www.hipchat.com/docs/apiv2/method/send_room_notification
* Visit https://YOURCOMPANY.hipchat.com/rooms/tokens/ROOM_ID create your notification token `AUTH_TOKEN`.
* Store the AUTH_TOKEN and the ROOM_ID in `$HOME/hipchat_settings` file on your Acquia Cloud Server (see hipchat_settings file).
* Set the execution bit to on e.g. `chmod a+x hipchat_settings`
* Add `hipchat.sh` to dev, test, prod or common __post-cody-deploy__ hook.


