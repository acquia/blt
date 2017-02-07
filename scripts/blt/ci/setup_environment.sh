#!/usr/bin/env bash

set -ev

# Get the latest version of composer.
composer self-update
composer global require "hirak/prestissimo:^0.3"

set +v
