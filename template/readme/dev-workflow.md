# Development Workflow

“How do I contribute code to this project?”

First off, take a moment to review our [Best Practices](best-practices.md)before writing or submitting any code.

## Git Workflow

No direct changes should be pushed to the Acquia repository. The process of syncing these repositories is managed transparently in the background.

The recommended workflow resembles a [Gitflow Workflow](https://www.atlassian.com/git/workflows#!workflow-gitflow) with the follow specifics -

* All development is performed against a `develop` branch.
* Completed features are merged into a `release` branch until a new release needs to be made. Additional QA testing should be made against this branch and fixed inline, if needed.
* Each commit to the `master` branch is tagged with a release number, named either based on sprints (e.g. `24.0`) or date (e.g. `2014-08-19.0`).
* Any hotfixes are merged directly into a `hotfix` branch, which can then be merged to `master`.

## Beginning work locally

1. Pull a ticket in JIRA
1. Create a new local feature branch named according to the following pattern:
  `abc-123-short-desc` Where "ABC" is the Jira prefix of your Jira project and "123" is the ticket number for which the work is being performed.
1. Make your code changes.
1. Commit your changes. Each commit should be logically atomic, and your commit messages should follow the pattern: "ABC-123 A grammatically correct sentence ending within punctuation."

## Creating a Pull Request

For any work, pull requests must be created for individual tasks and submitted for review. Before submitting a pull request, be sure to [sync the local branch](https://help.github.com/articles/syncing-a-fork) with the upstream primary branch -

    git checkout develop
    git pull upstream develop
    git push origin develop
    git checkout -b XXX-<new-issue-branch> develop

If you created many small commits locally while working through a ticket, you should clean the history so that it can be easily reviewed. You can combine these commits using `git rebase`.

    git rebase -i upstream/master

Pull requests should never contain merge commits from upstream changes.

Push your feature branch to your fork of the upstream repository, and submit a Pull Request from your-fork/feature-branch to canonical-repo/develop. You may optionally use [Hub](https://github.com/github/hub) to submit your pull request from the command line.

    hub pull-request

In order to enforce consistency on a project, a pull request template can also be configured using `hub` -

    git config --global --add hub.pull-request-template-path ~/.pr-template

## Resolving merge conflicts

Merge conflicts result when multiple developers submit PRs modifying the same code and Git cannot automatically resolve the conflict. For instance, if two developers add update hooks to the same module at the same time, these will necessarily conflict, because update hooks must be numbered in a defined sequence.

Developers are responsible for fixing merge conflicts on their own PRs. Follow this process to resolve a merge conflict:

1. Fetch upstream history: `git fetch upstream`
2. Check out the branch against which you opened your PR (e.g. master): `git checkout master`
3. Make sure it matches upstream: `git  reset --hard upstream/master`
4. Check out your feature branch: `git checkout feature/foo`
5. Merge master (this is where the magic happens): `git merge master`
6. At this point, Git will complain about a merge conflict. Run `git status` to find the conflicting file(s).
7. Edit the files to fix the conflict. The resources at the end of this section provide more information on this process.
8. Use `git add` to add all of the files you fixed.
9. Finally, run `git commit` to finish the merge, and `git push origin feature/foo` to update the PR.

Additional resources:

- https://confluence.atlassian.com/bitbucket/resolve-merge-conflicts-704414003.html
- https://githowto.com/resolving_conflicts

## Integration (merging pull requests)

Two versions of the integration workflow are recommended -

1. Integration manager
1. Peer review

**In either workflow, no one should ever commit their own code to the primary working branch.**

### Integration Manager

This model requires one (or more) lead developers to take the responsibility of merging all pull requests. This ensures consistency in quality control as well as identifying any potential issues with related, open pull requests.

A small group of one or more person(s) is selected to be integrators. All commits are reviewed by this group. If work is done by an integrator, their work should be reviewed by a fellow integrator (as if they were a developer).

### Peer Review

This model removes the bottleneck of designated integrators, but still eliminates commits directly to the working branch. In short, every commit is reviewed by a developer other than the one submitting the original commit.

## Continuous Integration

After a Pull Request has been submitted or merged, our continuous integration solution will automatically build a site artifact, install an ephemeral instance of Drupal, and execute tests against it. For more information on the build process, please see the [build directory](../build/README.md).

## Deployment on Cloud

Once work has been merged on GitHub and tested via the CI solution, a separate production-ready built artifact will be built and deployed to Acquia Cloud. This can be done either manually or automatically.

Please see [deploy.md](deploy.md) for more information.

## Release Process

A designated Release Master will perform the release to production. This is typically the project’s Technical Architect. See the [Release Process document](release-process.md) for detailed information.
