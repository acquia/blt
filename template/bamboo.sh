#!/usr/bin/env bash

# This is an example Bamboo script to run a deployment.
# You must set up a Bamboo server with PHP, Composer, and PHPUnit.
# Create a job
# Create a `Source Code Checkout` task that checks out the `develop` or relevant branch.
# Create a `Script` task,
# - Set `Script location` to `File`.
# - Set `Script file` to `bamboo.sh`.

# Set the branch.
branch=develop

# Run composer install.
composer install

# Run validation tasks.
./blt.sh -Dbehat.run-server=true -Dcreate_alias=false -Dbehat.launch-phantom=true build:validate:test

# Run deployment.
./blt.sh deploy:artifact -Ddeploy.commitMsg="Automated commit by Bamboo for $branch" -Ddeploy.branch="$branch-build"
