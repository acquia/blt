#!/bin/sh
#
# This sample a Cloud Hook script to update New Relic whenever there is a new code deployment 

site=$1         # The site name. This is the same as the Acquia Cloud username for the site.
targetenv=$2    # The environment to which code was just deployed.
sourcebranch=$3 # The code branch or tag being deployed.  
deployedtag=$4  # The code branch or tag being deployed. 
repourl=$5      # The URL of your code repository.
repotype=$6     # The version control system your site is using; "git" or "svn".


#Load the New Relic APPID and APPKEY variables.
. $HOME/newrelic_settings

curl -s -H "x-api-key:$APIKEY" -d "deployment[application_id]=$APPID" -d "deployment[host]=localhost" -d "deployment[description]=$deployedtag deployed to $site:$targetenv" -d "deployment[revision]=$deployedtag" -d "deployment[changelog]=$deployedtag deployed to $site:$targetenv" -d "deployment[user]=$username"  https://rpm.newrelic.com/deployments.xml
