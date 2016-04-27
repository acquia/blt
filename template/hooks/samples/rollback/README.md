# Code Deploy Rollback

Author: Erik Webb

### Purpose

This hook utilizes the simpletest module to test code base during deployment and automatically 
rollback to the last deployed set of code on test failure. Since pre-code-deploy hooks don't exist
yet we store original code source in the origsource variable in the rollback settings file stored in
the $HOME dir. This file also lists drush test-run tests to be run and the number of attempts to make 
before giving up. 

### Example Scenario

### Installation Steps


Installation Steps (assumes ah cloud hooks installed in Version Control Software)

* Copy rollback.sh into your dev, stage, prod, or common hooks directory.
* SCP or SFTP rollback_settings to your $HOME dir on your Acquia Host Server. 
* $TEST settings are available SimpleTests (or core Testing module in D7+). You may use '--all' for all tests (very slow). See http://drupal.org/simpletest for details.
* Edit rollback_settings to your existing code base ($ORIGSOURCE), test setting ($TEST) and number of attempts ($ATTEMPTS). Ensure execute bits are set on both files. (i.e. chmod a+x rollback_settings and chmod a+x rollback.sh)

  

