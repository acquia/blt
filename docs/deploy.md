# Deployment workflow

"How do I deploy code from my local machine, or GitHub, to the Acquia Cloud?"

For information on how to deploy to production, see [release-process.md](release-process.md).

This document outlines the workflow to build a complete Drupal docroot (plus supporting features, such as Cloud Hooks) which can be deployed directly to Acquia Cloud. Collectively, this bundle of code is referred to as the "build artifact."

The most important thing to remember about this workflow is that the GitHub and Acquia Cloud repos are _not_ clones of one another. GitHub only stores the source code, and Acquia Cloud only stores the production code (i.e., the build artifacts).

Currently, this workflow can either be followed manually, or integrated into a CI solution such as Acquia Pipelines, Travis CI, or Jenkins.

## First time setup

You should have your GitHub repository (where this document is stored) checked out locally. Your Acquia Cloud repository should be empty, or nearly empty.

Check out a new branch to match whatever branch you are working on in GitHub (typically `develop`).

Ensure your Acquia Cloud remote is listed in `blt.yml` under `git:remotes`, e.g.:

```yaml
git:
  default_branch: master
  remotes:
    cloud: 'project@svn-1234.devcloud.hosting.acquia.com:project.git'
```

## Creating the build artifact

In order to create the build artifact in `/deploy`, simply run

    blt artifact:build

This task is analogous to `source:build` but with a few critical differences:

* The docroot is created at `/deploy/docroot`
* Only production required to the docroot
* (planned) CSS / JS are compiled in production mode (compressed / minified)
* (planned) Sensitive files, such as CHANGELOG.txt, are removed

After the artifact is created, you can inspect it or even run it as a website locally. You may also manually commit and push it to Acquia Cloud.

## Create and deploy the build artifact

To both create and deploy the build artifact in a single command, run the following command

    blt artifact:deploy --commit-msg "BLT-000: Example deploy to branch" --branch "develop-build" --no-interaction

This command will commit the artifact to the `develop-build` branch with the specified commit message and push it to the remotes defined in blt.yml.

To create a new git tag for the artifact (rather than committing to a branch) run:

    blt artifact:deploy --commit-msg "Creating release 1.0.0." --tag "1.0.0"

This will generate the artifact, tag it with `1.0.0`, and push it to the remotes defined in blt.yml.

When deploying a tag to the artifact repo, if the config option `deploy.tag_source` is set to TRUE, BLT will also create the supplied tag on the source repository. This makes it easier to verify the source commit upon which an artifact tag is based.

*Note* however that BLT _does not_ automatically push the tag created on the source repository to its remote.

## Modifying the artifact

The artifact is built by running the `artifact:build` target, which does the following:

* Rsyncs files from the repository root
* Re-builds dependencies directly in the deploy directory, e.g., `composer install`

The rsync and re-build processes can be configured by modifying the values of variables under the top-level `deploy` key in your blt.yml file.

See [Extending BLT](extending-blt.md) for more information on overriding default configuration.

### Debugging deployment artifacts

If you would like to create, commit, but _not push_ the artifact, you may do a dry run:

    blt artifact:deploy -D deploy.dryRun=true

This is helpful for debugging deployment artifacts.

## Continuous integration

Instead of performing these deployments manually, you can enlist the help of a CI tool such as Acquia Pipelines, Travis CI, or Jenkins. This will allow you to generate deployment artifacts automatically whenever code is merged into a given branch. Please see [Continuous Integration](ci.md) for information on configuring a CI tool.

## Cloud Hooks

On Acquia Cloud, [Cloud Hooks](https://docs.acquia.com/acquia-cloud/develop/api/cloud-hooks/) are the preferred method to run database updates and configuration imports on each deploy. BLT provides a post-code-deploy hook that will conveniently run these updates automatically and fail the deployment task in Insight if anything goes wrong.

To install Acquia Cloud hooks for your BLT project:

1. Initialize Acquia Cloud hooks

        blt recipes:cloud-hooks:init

    This will add a hooks directory in your project root based on [BLT's default Acquia Cloud hooks](https://github.com/acquia/blt/tree/10.0.x/scripts/cloud-hooks/hooks).

1. Commit the new directory and push it to your Acquia git remote. Example commands:

        git add hooks
        git commit -m 'Initializing Acquia Cloud hooks.'
        git push origin


For consistency and reliability, you should run the same updates on deployment as you would run locally or in CI testing. BLT provides aliases for the `drupal:update` task to support this in a local environment and `artifact:update:drupal` to execute against an artifact.

If your team uses Slack, you can also be notified of each successful or failed deployment. Simply set up an incoming webhook in your Slack team to receive the notification (see the API documentation at https://api.slack.com/), and then store the webhook URL in `slack.webhook-url` in `blt/blt.yml`. You may also set it as an environmental variable `SLACK_WEBHOOK_URL`.

For more information, see the [Acquia Cloud Hooks Slack example](https://github.com/acquia/cloud-hooks/tree/master/samples/slack).
