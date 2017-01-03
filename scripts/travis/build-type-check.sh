#!/usr/bin/env bash

if [ "$TRAVIS_EVENT_TYPE" = "cron" ]; then
    export BLT_BUILD_TYPE="nightly"

elif [ "$TRAVIS_EVENT_TYPE" = "pull_request" ]; then
    export BLT_BUILD_TYPE="pr"

elif [ -n "$TRAVIS" ]; then
    export BLT_BUILD_TYPE="deploy"

else
    # If you see local printed in travis-ci you have a problem.
    export BLT_BUILD_TYPE="local"
fi

echo "Setting BLT_BUILD_TYPE to:" $BLT_BUILD_TYPE
