#!/usr/bin/env bash

# This script will be run every single time that `blt update` is executed.

echo "Removing deprecated BLT files from project."
rm -blt.sh
rm -rf build
rm -rf tests/phpunit/blt
rm -rf scripts/blt
rm -rf scripts/drupal
rm -rf scripts/drupal-vm
rm -rf scripts/git-hooks
rm -rf scripts/release-notes
rm -rf scripts/tugboat
rm readme/acsf-setup.md
rm readme/architecture.md
rm readme/best-practices.md
rm readme/deploy.md
rm readme/dev-workflow.md
rm readme/features-workflow.md
rm readme/local-development.md
rm readme/onboarding.md
rm readme/project-tasks.md
rm readme/release-process.md
rm readme/repo-architecture.md
rm readme/views.md
