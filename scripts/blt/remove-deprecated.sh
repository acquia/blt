#!/usr/bin/env bash
# This script will be run every single time that `blt update` is executed.

rm -rf build
rm -rf docroot/sites/default/settings/apcu_fix.yml
rm -rf docroot/sites/default/settings/base.settings.php
rm -rf docroot/sites/default/settings/blt.settings.php
rm -rf docroot/sites/default/settings/cache.settings.php
rm -rf docroot/sites/default/settings/filesystem.settings.php
rm -rf docroot/sites/default/settings/includes.settings.php
rm -rf docroot/sites/default/settings/logging.settings.php
rm -rf docroot/sites/default/settings/travis.settings.php
rm -rf docroot/sites/default/settings/pipelines.settings.php
rm -rf docroot/sites/default/settings/tugboat.settings.php
rm -rf tests/phpunit/blt
rm -rf tests/phpunit/Bolt
rm -rf scripts/blt
rm -rf scripts/drupal
rm -rf scripts/drupal-vm
rm -rf scripts/git-hooks
rm -rf scripts/release-notes
rm -rf scripts/tugboat
rm -f blt.sh
rm -f project.yml
rm -f project.local.yml
rm -f example.project.local.yml
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
