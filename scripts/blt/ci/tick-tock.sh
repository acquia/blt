#!/usr/bin/env bash
# This command will simulate output, preventing timeouts for long-running
# commands that do not produce output.

set -e
set -u

command=$@

# launch command in the background
${command} &

# ping every second
seconds=0
limit=40*60
while kill -0 $! >/dev/null 2>&1;
do
    echo -n -e " \b" # never leave evidence

    if [ $seconds == $limit ]; then
        break;
    fi

    seconds=$((seconds + 1))

    sleep 1
done

exit $?
