# Deployment workflow

"How do I deploy code from my local machine, or GitHub, to the Acquia Cloud?"

For information on how to deploy to production, see [release-process.md](release-process.md).

This document outlines the workflow to build a complete Drupal docroot (plus supporting features, such as Cloud Hooks) which can be deployed directly to Acquia Cloud. Collectively, this bundle of code is referred to as the "build artifact".

The most important thing to remember about this workflow is that the Github and Acquia Cloud repos are _not_ clones of one another. Github only stores the source code, and Acquia Cloud only stores the production code (i.e. the build artifacts).

Currently, this workflow can either be followed manually, or integrated into a CI solution such as Acquia Pipelines, Travis CI, or Jenkins.

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

    blt deploy --commit-msg "BLT-000: Example deploy to branch" --branch "develop-build" --no-interaction

This command will commit the artifact to the `develop-build` branch with the specified commit message and push it to the remotes defined in project.yml.

To create a new git tag for the artifact (rather than committing to a branch) run:

    blt blt deploy --commit-msg "Creating release 1.0.0." --tag "1.0.0"

This will generate the artifact, tag it with `1.0.0`, and push it to the remotes defined in project.yml.

## Modifying the artifact

The artifact is built by running the `deploy:build` target, which does the following:

* Rsyncs files from the repository root
* Re-builds dependencies directly in the deploy directory. E.g., `composer install`

The rsync and re-build processes can be configured by modifying the values of variables under the top-level `deploy` key in your project.yml file.

See [Extending BLT](extending-blt.md) for more information on overriding default configuration.

### Debugging deployment artifacts

If you would like to create, commit, but _not push_ the artifact, you may do a dry run:

    blt deploy -D deploy.dryRun=true

This is helpful for debugging deployment artifacts.

## Continuous integration

Instead of performing these deployments manually, you can enlist the help of a CI tool such as Acquia Pipelines, Travis CI, or Jenkins. This will allow you to generate deployment artifacts automatically whenever code is merged into a given branch. Please see [Continuous Integration](ci.md) for information on configuring a CI tool.

## Cloud Hooks

On Acquia Cloud, [Cloud Hooks](https://docs.acquia.com/cloud/manage/cloud-hooks) are the preferred method to run database updates and configuration imports on each deploy. BLT provides a post-code-deploy hook that will conveniently run these updates automatically and fail the deployment task in Insight if anything goes wrong.

To install Acquia Cloud hooks for your BLT project:

1. Initialize Acquia Cloud hooks

        blt setup:cloud-hooks

    This will add a hooks directory in your project root based on [BLT's default Acquia Cloud hooks](https://github.com/acquia/blt/tree/8.x/scripts/cloud-hooks/hooks).

1. Commit the new directory and push it to your Acquia git remote. Example commands:

        git add hooks
        git commit -m 'Initializing Acquia Cloud hooks.'
        git push origin

For consistency and reliability, you should run the same updates on deployment as you would run locally or in CI testing. BLT provides aliases for the `setup:update` task to support this, such as `local:update` and `deploy:update`. These aliases all run the same updates, but with the appropriate aliases and configuration directories for each environment.

If your team uses Slack, you can also be notified of each successful or failed deployment. Simply set up an incoming webhook in your Slack team to receive the notification (see the API documentation at https://api.slack.com/), and then store the webhook URL in a `$HOME/slack_settings` file on your Acquia Cloud servers:

    SLACK_WEBHOOK_URL=https://hooks.slack.com/services/xxx/yyy/zzz

For more information, see the [Acquia Cloud Hooks Slack example](https://github.com/acquia/cloud-hooks/tree/master/samples/slack).
