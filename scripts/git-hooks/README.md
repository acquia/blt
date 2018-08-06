# Git Configuration

This directory contains
[git hooks](https://git-scm.com/book/en/v2/Customizing-Git-Git-Hooks)
These should be symlinked into your local repository's `.git` directory using
the `blt:init:git-hooks` task during the
[onboarding process](http://blt.readthedocs.io/en/latest/readme/onboarding).

## Provided Hooks

Two default hooks are provided:

* _commit-msg_ - This validates the syntax of a git commit message before it is committed locally.
* _pre-commit_ - This runs Drupal Code Sniffer on committed code before it is committed locally.
