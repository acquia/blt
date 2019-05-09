#!/usr/bin/env bash

# NAME
#     _includes.sh - Include reusable code.
#
# SYNOPSIS
#     cd "$(dirname "$0")"; source _includes.sh
#
# DESCRIPTION
#     Includes common features used by the Travis CI scripts.

# Exit early on standard ORCA jobs.
[[ "$ORCA_JOB" ]] && exit 0

# Reuse ORCA's own includes.
source ../../../orca/bin/travis/_includes.sh
