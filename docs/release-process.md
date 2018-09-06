# Release Process

## Branching strategies

See the [Git Workflow section of the Dev Workflow document](dev-workflow.md#git-workflow) for this information.

## Generating a build artifact

See [Create and deploy the build artifact](deploy.md#create-and-deploy-the-build-artifact) in [deploy.md](deploy.md).

## Tagging

Once the `master` branch contains all of the desired commits for a release (regardless of the [Git Workflow](dev-workflow.md#git-workflow) your team employed to arrive at the updated branch), a [tag](https://git-scm.com/book/en/v2/Git-Basics-Tagging) should be created. It is common to use semantic versioning to name tags, e.g., `1.0.0`, `1.2.3`, etc.

A tag can be created by checking out the `master` branch locally and executing the `git tag` command:

```
git checkout master
git tag 1.0.0
```

If you have a [continuous integration](ci.md) setup via Travis CI or Pipelines, upon pushing the "source tag" to your GitHub repository, an "artifact tag" corresponding to your source tag will be created and pushed to Acquia Cloud with the same name, but "-build" tacked onto the end. A `1.0.0` source tag, for example, would end up generating a `1.0.0-build` tag.

If you are doing deployments manually, you will want to checkout your `master` branch locally, and [manually build a deployment artifact](deploy.md#creating-the-build-artifact) based off of that. Even if you build the deployment artifact manually, the recommendation is to still push up a source tag (e.g., `1.0.0`) based on your `master` branch to your repository.

## Deploying tag and executing updates

Deploying Drupal across environments can be daunting, but if due diligence has been taken with configuration management, the process of deployment is actually quite simple.

No matter how many environments there are or whatever versioning workflow is being used, the actual deployment process will take approximately the following form (please note the commands are examples):

1. Put the site into maintenance mode `drush vset maintenance_mode 1`
2. Flush Caches to empty the cache tables and ensure maintenance mode is set. `drush cc all`
3. Perform any necessary backups, notably the database `drush sql-dump > backup-yyyy-mm-dd.sql`
4. Pull the latest code onto the server `git pull origin/master`
5. Run update.php `drush updb -y`
7. Take the site out of maintenance mode `drush vset maintenance_mode 0`
8. Clear Drupal caches `drush cc all`

A few things that you should (almost) never do on production:
1. Revert all features via `drush fra -y`. This poses a site stability risk and also risks wiping a feature that may be been accidentally overridden in production. Feature should be explicitly reverted via a call to `features_revert_module()` in a `hook_update_N()` implementation.
1. Run `drush cc all`. Specific caches should be targeted whenever possible.
1. Utilize `drush use`. This introduces the risk that the release master will accidentally run a command against prod after the release.

There might be some extra steps depending on the infrastructure and the extent of site changes. For example, a major application change might require a flush of other caches in the system such as Varnish or Memcached.

# Notifications

You can configure various tools to provide notifications of deployment related events. For instance:

* [Travis CI](https://docs.travis-ci.com/user/notifications/) can notify you about your build results through email, IRC, and/or webhooks.
* Jenkins has plugins to provide build notifications via [Slack](https://wiki.jenkins-ci.org/display/JENKINS/Slack+Plugin), [IRC](https://wiki.jenkins-ci.org/display/JENKINS/IRC+Plugin), and many more services.
* You can use [Acquia Cloud Hooks](https://docs.acquia.com/cloud/manage/cloud-hooks#animated) to provide deployment, db, or code related notification to service such as:
    * New Relic
    * Slack
    * HipChat

## Resources:

* [Connecting the Tubes: JIRA, GitHub, Jenkins, and Slack](https://dev.acquia.com/blog/connecting-tubes-jira-github-jenkins-and-slack)
