# Development Workflow

“How do I contribute code to this project?”

## Git Workflow

No direct changes should be pushed to the build-artifact repository. The process of syncing these repositories is managed transparently in the background.

There are a few recommended Git workflows to consider, with the size of the team being a large influencing factor as to which workflow you might go with.

### Feature Branch Workflow

The [Feature Branch Workflow](https://www.atlassian.com/git/tutorials/comparing-workflows#feature-branch-workflow) encourages all feature development work to take place on a dedicated branch, instead of committing locally to the standard `master` branch. The specifics are as follows:

* A developer creates a new branch based on an up-to-date `master` branch to start work on a new feature.
* When the work is completed, the "feature branch" is pushed to `origin` (or whatever name the developer gave the remote of their forked repository).
* A pull request is opened against the `master` branch, giving other team members the chance to review the work completed prior to merging into `master`.
* Once the work is accepted, it is merged into the `master` branch.

The above flow is best-suited for a small team. For a larger team, however, the Gitflow Workflow should be considered.

### Gitflow Workflow

The [Gitflow Workflow](https://www.atlassian.com/git/tutorials/comparing-workflows#gitflow-workflow) builds on the concept of the feature branch workflow. In addition to developers committing to feature branches instead of directly to `master`, they will additionally submit pull requests against a `develop` branch, that serves as an integration branch for new features. The specifics are as follows:

* A developer creates a new branch based on an up-to-date `develop` branch to start work on a new feature.
* When the work is completed, the "feature branch" is pushed to `origin`.
* A pull request is opened against the `develop` branch, giving other team members the chance to review the work completed prior to merging into `develop`.
* Once the desired set of features has been merged into the `develop` branch (or as the Atlassian docs mention, a predetermined release date has approached) a new `release` branch is created off of `develop`.
* From then on, the `release` branch can be worked on by one team or the release master to add only what is necessary for the release, while the rest of the team is able to continue feature development against the `develop` branch.
* The `release` branch is eventually merged into `master`, and `develop` rebased onto `master` upon merging.

This flow allows a larger team to work off an integrated branch (`develop`), all while maintaining a stable `master` branch that remains in a good state.

### Gitflow Workflow (abridged version)

The [Gitflow Workflow](https://www.atlassian.com/git/workflows#!workflow-gitflow) builds on the concept of the feature branch workflow. In addition to developers committing to feature branches instead of directly to `master`, they will additionally submit pull requests against a `develop` branch, that serves as an integration branch for new features. The specifics are as follows:

* A developer creates a new branch based on an up-to-date `develop` branch to start work on a new feature.
* When the work is completed, the "feature branch" is pushed to `origin`.
* A pull request is opened against the `develop` branch, giving other team members the chance to review the work completed prior to merging into `develop`.
* Once the desired set of features has been merged into the `develop` branch and the branch is known to be in a good state, it is merged into the `master` branch.

This flow still allow a team to work off an integrated branch (`develop`), all while maintaining a stable `master` branch, but removes the `release` branch portion of the flow.

**Note:** In all flows above, hotfixes are merged directly into a `hotfix` branch, which can then be merged to `master`.

## Workflow Example: Local Development

1. Pull a ticket in JIRA
1. Fetch upstream to ensure most current code `$ git fetch upstream`
1. Create a new local feature branch named according to the following pattern:
  `abc-123-short-desc` Where "ABC" is the Jira prefix of your Jira project and "123" is the ticket number for which the work is being performed. `git checkout -b abc-123-short-desc upstream/master`
1. Reset your local environment (if necessary) to a clean state with either `$ blt setup` or `$ blt sync`
1. Make your code changes
1. Commit your changes. Each commit should be logically atomic, and your commit messages should follow the pattern: "ABC-123 A grammatically correct sentence ending within punctuation."
1. Run Tests / Validation Scripts `$ blt validate` and `$ blt tests`
1. Ensure no additional changes have been made to the upstream repository `$ git fetch upstream` and rebase if necessary `$ git rebase upstream/master`
1. Push work to your forked repository (origin) so a Pull Request may be created `$ git push --set-upstream origin abc-123-short-desc`

## Creating a Pull Request

Pull requests should never contain merge commits from upstream changes. These are avoided by using the `$ git rebase` command instead of pulling / merging.

Push your feature branch to your fork of the upstream repository, and submit a Pull Request from your-fork/feature-branch to canonical-repo/develop. You may optionally use [Hub](https://github.com/github/hub) to submit your pull request from the command line.

    hub pull-request

In order to enforce consistency on a project, a pull request template can also be configured using `hub` -

    git config --global --add hub.pull-request-template-path ~/.pr-template

## Resolving merge conflicts

Merge conflicts result when multiple developers submit PRs modifying the same code and Git cannot automatically resolve the conflict. For instance, if two developers add update hooks to the same module at the same time, these will necessarily conflict, because update hooks must be numbered in a defined sequence.

Developers are responsible for fixing merge conflicts on their own PRs. Follow this process to resolve a merge conflict:

1. Fetch upstream history: `git fetch upstream`
2. Check out the branch against which you opened your PR (e.g., master): `git checkout master`
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

After a Pull Request has been submitted or merged, our continuous integration solution will automatically build a site artifact, install an ephemeral instance of Drupal, and execute tests against it. For more information on the build process, please see the [Continuous Integration documentation](ci.md).

## Deployment on Cloud

Once work has been merged on GitHub and tested via the CI solution, a separate production-ready built artifact will be built and deployed to Acquia Cloud. This can be done either manually or automatically.

Please see [deploy.md](deploy.md) for more information.

## Release Process

A designated Release Master will perform the release to production. This is typically the project’s Technical Architect. See the [Release Process document](release-process.md) for detailed information.
