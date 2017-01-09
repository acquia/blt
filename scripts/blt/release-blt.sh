#!/usr/bin/env bash

# Usage ./scripts/blt/release-blt 8.6.11 [github-token]

echo "This will perform a hard reset on the current repository and create a draft release for BLT."
read -p "Press any key to continue."

git clean -fd .
git reset --hard
git checkout 8.x
git pull origin 8.x
github_changelog_generator -t $TOKEN --future-release=$tag
RELEASE_NOTES=$(cat ${SCRIPT_DIR}../../temp-changelog.md})
API_JSON=$(printf '{"tag_name": "%s","target_commitish": "8.x-release","name": "v%s","body": " %s","draft": true,"prerelease": true}' $tag $tag $RELEASE_NOTES)
curl --data "$API_JSON" https://api.github.com/repos/acquia/blt/releases?access_token=$TOKEN
