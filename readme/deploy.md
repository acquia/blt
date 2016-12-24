# Deployment workflow

"How do I deploy code from my local machine, or GitHub, to the Acquia Cloud?"

For information on how to deploy to production, see [release-process.md](release-process.md).

This document outlines the workflow to build a complete Drupal docroot (plus supporting features, such as Cloud Hooks) which can be deployed directly to Acquia Cloud. Collectively, this bundle of code is referred to as the "build artifact".

The most important thing to remember about this workflow is that the Github and Acquia Cloud repos are _not_ clones of one another. Github only stores the source code, and Acquia Cloud only stores the production code (i.e. the build artifacts).

Currently, this workflow can either be followed manually, or integrated into a CI solution such as Jenkins or Travis. Acquia Pipelines enables your build to run directly on the Acquia platform, integrating the separate repositories for development workflow (Github) and production code (Acquia Cloud).

## First time setup

You should have your Github repository (where this document is stored) checked out locally. Your Acquia Cloud repository should be empty, or nearly empty.

Check out a new branch to match whatever branch you are working on in Github (typically `develop`).

Ensure your Acquia Cloud remote is listed in project.yml under git:remotes.

## Creating the build artifact

In order to create the build artifact in `/deploy`, simply run

    blt deploy:build

This task is analogous to `setup:build` but with a few critical differences:

* The docroot is created at `/deploy/docroot`.
* Only production required to the docroot
* (planned) CSS / JS are compiled in production mode (compressed / minified)
* (planned) Sensitive files, such as CHANGELOG.txt, are removed.

After the artifact is created, you can inspect it or even run it as a website locally. You may also manually commit and push it to Acquia Cloud.

## Create and deploy the build artifact

To both create and deploy the build artifact in a single command, run the following command

    blt deploy -Ddeploy.branch=develop-build -Ddeploy.commitMsg='BLT-123: The commit message.'

This command will commit the artifact to the `develop-build` branch with the specified commit message and push it to the remotes defined in project.yml.

## Modifying the artifact

The artifact is built by running the `deploy:build` target, which does the following:

* Rsyncing files from the repository root
* Re-building dependencies directly in the deploy directory. E.g., `composer install`

The rsync and re-build processes can be configured using the `deploy` Phing variable.

See [Extending BLT](extending-blt.md) for more information on overriding Phing variables.

### Debugging deployment artifacts

If you would like to create, commit, but _not push_ the artifact, you may do a dry run:

    blt deploy -Ddeploy.branch=develop-build -Ddeploy.commitMsg='BLT-123: The commit message.' -Ddeploy.dryRun=true

This is helpful for debugging deployment artifacts.

## Continuous integration

Instead of performing these deployments manually, you can enlist the help of a CI tool such as Travis or Jenkins.

### Travis CI

Access to Travis is already provided by Acquia PS, which makes this option appealing on a cost basis. It will automatically deploy new commits after they are merged and tests pass. However, it's somewhat insecure (you have to create an SSH key for deployments that can be accessed by any developer), and it's impossible to schedule regular deployments or perform more advanced integrations.

For more information on configuring Travis CI, see "Setting Up Travis CI for automated deployments" in [Continuous Integration](ci.md).

### Jenkins (Cloudbees)

Jenkins is more expensive and time-consuming to set up, but offers much more flexibility. For instance, you can add a Slack slash command (such as `/deploy`) that allows your QA team to deploy specific branches to Acquia Cloud on demand. See the forthcoming README on Jenkins integration for more information.

Note, we previously used Engineering's Jenkins instance for CI (ci.acquia.com), but this should be avoided going forward. Instead, a starter account with Cloudbees is recommended. There's a two-week free trial, and after that the service is ~$60 per month, and must be paid for on a per-project basis.

## Cloud Hooks

On Acquia Cloud, [Cloud Hooks](https://docs.acquia.com/cloud/manage/cloud-hooks) are the preferred method to run database updates and configuration imports on each deploy. BLT provides a post-code-deploy hook that will conveniently run these updates automatically and fail the deployment task in Insight if anything goes wrong.

For consistency and reliability, you should run the same updates on deployment as you would run locally or in CI testing. BLT provides aliases for the `setup:update` task to support this, such as `local:update` and `deploy:update`. These aliases all run the same updates, but with the appropriate aliases and configuration directories for each environment.
