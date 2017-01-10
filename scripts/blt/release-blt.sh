#!/usr/bin/env bash

# Usage ./scripts/blt/release-blt [tag] [github-token]

tag="$1"
TOKEN="$2"

SCRIPT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
echo "This will perform a hard reset on the current repository and create a draft release for BLT."
read -p "Press any key to continue."

set -x
git clean -fd .
git remote update
git reset --hard
git checkout 8.x
git reset --hard origin/8.x
github_changelog_generator --token=${TOKEN} --future-release=$tag --output=CHANGELOG.partial

# Remove last 3 lines from CHANGELOG.partial.
head -n -3 CHANGELOG.partial > CHANGELOG.partial.trimmed ; mv CHANGELOG.partial.trimmed CHANGELOG.partial
RELEASE_NOTES=$(cat CHANGELOG.partial.trimmed)
# Append CHANGELOG.md to CHANGELOG.partial.
cat CHANGELOG.md >> CHANGELOG.partial
# Replace CHANGELOG.md with CHANGELOG.partial contents.
mv CHANGELOG.partial CHANGELOG.md

git checkout 8.x-release
git merge 8.x
git push origin 8.x-release
API_JSON=$(printf '{"tag_name": "%s","target_commitish": "8.x-release","name": "%s","body": "%s","draft": true,"prerelease": true}' $tag $tag $RELEASE_NOTES)
curl --data "$API_JSON" https://api.github.com/repos/acquia/blt/releases?access_token=$TOKEN
