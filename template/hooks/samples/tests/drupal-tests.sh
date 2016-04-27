#!/bin/bash
#
# Cloud Hook: drupal-tests
#
# Run Drupal simpletests in the target environment using drush test-run.

site="$1"
target_env="$2"

# Select the tests to run. Run "drush help test-run" for options.
TESTS="UserRegistrationTestCase"
# To run all tests (very slow!), uncomment this line.
TESTS="--all"

# Enable the simpletest module if it is not already enabled.
simpletest=`drush @$site.$target_env pm-info simpletest | perl -F'/[\s:]+/' -lane '/Status/ && print $F[2]'`
if [ "$simpletest" = "disabled" ]; then
    echo "Temporarily enabling simpletest module."
    drush @$site.$target_env pm-enable simpletest --yes
fi

# Run the tests.
drush @$site.$target_env test-run $TESTS

# If we enabled simpletest, disable it.
if [ "$simpletest" = "disabled" ]; then
    echo "Disabling simpletest module."
    drush @$site.$target_env pm-disable simpletest --yes
fi
