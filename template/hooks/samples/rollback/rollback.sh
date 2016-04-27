#!/bin/bash
#
# Cloud Hook: tests_rollback
#
# Run Drupal simpletests in the target environment using drush test-run. On failure,
# rollback to last deployed code set
#
# implements Cloud_hook post_code_deploy
# @todo needs to have pre_code_deploy for proper handling of files.
# 


site="$1"
target_env="$2"
sourcebranch=$3 # The code branch or tag being deployed. 
deployedtag=$4  # The code branch or tag being deployed. 
repourl=$5      # The URL of your code repository.
repotype=$6     # The version control system your site is using; "git" or "svn".

#load variable settings from $HOME/rollback_settings
#Check rollback_settings exists; if not alert and exit 
if [ -x "$HOME/rollback_settings" ]; then
    . $HOME/rollback_settings
  else
    echo "rollback_settings file not found in $HOME or not able to include (check execution bit)"
    exit 1
fi

#check attempts variable has any number of tries left. To prevent infinite loops.
if [ "$ATTEMPTS" -le 0  ]; then
    echo "Maximum Number of attempts exceeded! Exiting."
    exit 1
fi

#now set the variable and append it into the settings file. 
ORIGATTEMPTS=$ATTEMPTS
let "ATTEMPTS-=1"
sed -i "s/ATTEMPTS=$ORIGATTEMPTS/ATTEMPTS=$ATTEMPTS/" $HOME/rollback_settings

#initialize exit code so we can exit with 0 after rollback
extcode=0

# Enable the simpletest module if it is not already enabled.
simpletest=`drush @$site.$target_env pm-info simpletest | perl -F'/[\s:]+/' -lane '/Status/ && print $F[2]'`
if [ "$simpletest" = "disabled" ]; then
    echo "Temporarily enabling simpletest module."
    drush @$site.$target_env pm-enable simpletest --yes
fi

# Run the tests.
CMD=`drush @$site.$target_env test-run $TESTS`

#test output from drush 
if [ $? -ne 0 ]; then 

  #sanity check to make sure we have a $origsource to fall back to. 
  if [ $ORIGSOURCE ]; then 
    #if simpletests fail tell the user and launch a new job rolling back to the original source 
    echo "Testing failed on deploy rolling back to $ORIGSOURCE"
    echo "Executing: drush @$site.$target_env ac-code-path-deploy $ORIGSOURCE"   
    drush @$site.$target_env ac-code-path-deploy $ORIGSOURCE   
  else #something is very wrong should never get here, if we do notify and quit.
    echo "Cannot rollback. No fallback source identified."
    exit 1  
  fi
    #set exitcode to fail so this code base does not deploy
    extcode=1

else 
  
  #simpletests passed! Inform user then clear and set rollback_settings to new code base
  echo  "Testing passed on deploy of $deployedtag"
  sed -i "s/ORIGSOURCE=$ORIGSOURCE/ORIGSOURCE=$deployedtag/" $HOME/rollback_settings
  extcode=0

fi

# If we enabled simpletest, disable it.
if [ "$simpletest" = "disabled" ]; then
  echo "Disabling simpletest module."
  drush @$site.$target_env pm-disable simpletest --yes
fi

#cleanly exit  
exit $extcode






