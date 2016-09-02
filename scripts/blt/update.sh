#!/usr/bin/env bash
# This script will be run every single time that `blt update` is executed.

rm -rf build
rm -rf docroot/sites/default/settings
rm -rf tests/phpunit/blt
rm -rf scripts/blt
rm -rf scripts/drupal
rm -rf scripts/drupal-vm
rm -rf scripts/git-hooks
rm -rf scripts/release-notes
rm -rf scripts/tugboat
rm -f blt.sh
rm -f readme/acsf-setup.md
rm -f readme/architecture.md
rm -f readme/best-practices.md
rm -f readme/deploy.md
rm -f readme/dev-workflow.md
rm -f readme/features-workflow.md
rm -f readme/local-development.md
rm -f readme/onboarding.md
rm -f readme/project-tasks.md
rm -f readme/release-process.md
rm -f readme/repo-architecture.md
rm -f readme/views.md
