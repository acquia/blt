# Git Configuration

BLT provides [git hooks](https://git-scm.com/book/en/v2/Customizing-Git-Git-Hooks)
that should be symlinked into your local repository's `.git` directory using
the `blt:init:git-hooks` task during the
[onboarding process](https://docs.acquia.com/blt/developer/onboarding/).

These hooks should be used on all projects, as they will save developers time. In particular, the pre-commit hook will prevent a git commit if validation fails on the code being committed (which will also occur during blt:validate calls during continuous integration).

## Provided Hooks

Two default hooks are provided:

* _commit-msg_ - This validates the syntax of a git commit message before it is committed locally.
* _pre-commit_ - This runs Drupal Code Sniffer on committed code before it is committed locally.
