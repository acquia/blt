# Generate Release Notes Script

## Overview
Use a script compiles PR comments for a project into a Markdown file that can
be copy and pasted into GitHub release notes.

## Usage

### Required Inputs
* **username:** your GitHub username
* **password:** your GitHub password. Note that if you use two factor 
  authentication you will need to use an [Access Token](https://help.github.com/articles/creating-an-access-token-for-command-line-use/) 
  in lieu of a password.
* **repository:** the name of the GitHub repository (e.g. `https://github.com/acquia-pso/my-repo`)

### Simple usage

    php generate-release-notes.php github username:password org:repo-name:branch > release-notes.md

### Specify a start date

    php generate-release-notes.php github username:password org:repo-name:branch 1/30/2014 > release-notes.md

### Specify a start date and number of PRs

    php generate-release-notes.php github username:password org:repo-name:branch 1/30/2014 50 > release-notes.md

    # Example: Commit Message
