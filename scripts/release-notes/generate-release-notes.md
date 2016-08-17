# Generate Release Notes Script

## Overview
This script compiles PR comments for a project into a Markdown file that can be copy and pasted into Github release notes or a Confluence Page.

## Usage

### Github

#### Simple Usage
`php generate-release-notes.php github [github username]:[github password] [github project]:[github repository]:[branch] > release-notes.md`  
`php generate-release-notes.php github dan:password acquia-pso:client-repo:master > release-notes.md`

### Specify a start date
`php generate-release-notes.php github dan:password acquia-pso:client-repo:master 12/31/2014 > release-notes.md`

### Specify a start date and number of PRs
`php generate-release-notes.php github dan:password acquia-pso:client-repo:master 12/31/2014 15 > release-notes.md`

### Stash and Confluence
Since stash is hosted by a client or service, you must pass in the base path, for example `stash.client.com`. You should not include `https://`.

#### Simple Usage
`php generate-release-notes.php stash:[base path] [stash username]:[stash password] [stash project]:[stash repository]:[branch] > release-notes.md`  
`php generate-release-notes.php stash:stash.client.com dan:password client-project:client-repo:master > release-notes.md`

### Specify a start date
`php generate-release-notes.php stash:stash.client.com dan:password client-project:client-repo:master 12/31/2014 > release-notes.md`

### Specify a start date and number of PRs
`php generate-release-notes.php stash:stash.client.com dan:password client-project:client-repo:master 12/31/2014 15 > release-notes.md`

