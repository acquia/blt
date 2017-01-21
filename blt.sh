#!/usr/bin/env bash

# @todo Move this into bin/blt directly.

SCRIPT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
BIN=$(cd ${SCRIPT_DIR}/../../bin && pwd)
PHING=${BIN}/phing

# This script simply passes all arguments to Phing.
"${PHING}" -f "${SCRIPT_DIR}/phing/build.xml" -logger vendor.acquia.blt.phing.phingcludes.BltLogger "$@"
