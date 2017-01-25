#!/usr/bin/env bash
blt -Dbehat.run-server=true -Dcreate_alias=false -Dbehat.launch-phantom=true ci:build:validate:test -Dblt.verbose=true
