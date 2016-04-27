#!/usr/bin/env bash

# What does this script do?
# This script will watch for a Travis build on a specific
# $source_branch on the canonical GitHub repository and deploy build artifacts
# to $dest_branch on the git.remote specified in project.yml.

# How to use this script?
# This script should be executed in the `after_success` section of .travis.yml.
# It requires two arguments. Example call:
# `scripts/deploy/travis-deploy.sh master master-build`

source_branch=$1  # The branch to watch.
dest_branch=$2    # The branch to which the build artifact should be committed and deployed.
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

# Note that the canonical repository is watched. Commits to forked repositories
# will not trigger deployment unless DEPLOY_PR is true.
if [[ "${TRAVIS_PULL_REQUEST}" = "false" ]] || [[ "${DEPLOY_PR}" = "true" ]];
  then
    echo "Deployments will be triggered on the \"${source_branch}\" branch or on any tag."
    echo "Current branch is \"${TRAVIS_BRANCH}\"."

    # Trigger deployment if $source_branch parameters matches or this is a tag.
    if [[ "${TRAVIS_BRANCH}" = $source_branch ]] || [[ -n "${TRAVIS_TAG}" ]];
      then
        echo "Build artifact will be deployed."
        commit_msg="Automated commit by Travis CI for Build #${TRAVIS_BUILD_ID}";
        # Call the `deploy` Phing target, passing in required parameters.
        ${DIR}/../../bolt.sh deploy:artifact -Ddeploy.branch="${dest_branch}" -Ddeploy.commitMsg="${commit_msg}";
      else
        echo "Build artifact will NOT be deployed for this branch."
    fi
  else
    echo "Build artifacts are not deployed for Pull Requests."
fi
