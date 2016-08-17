## Setup

To set up this project with Tugboat, run the following commands from the project root:

```
blt ci:tugboat:init
```

## Workflow

This workflow is for a brand new repository for which a base preview has never been built or committed.

### Initial Setup
1. Visit `https://dashboard.tugboat.qa/[project]/latest` to access the Base Preview.
2. Initialize the Base Preview, click Actions -> init.
3. Makefile -> tugboat-init is triggered.
4. Commit the Base Preview, click actions -> commit.

### Dev Workflow
5. Submit a Pull Request via GitHub, feature-branch-1 against master.
6. A new container with a clone of Base Preview is created for the PR.
8. Tugboat merges feature-branch-1 into master in PR Preview.
9. Makefile -> tugboat-build is triggered.
10. Merge Pull Request on GitHub.
11. Tugboat deletes the PR Preview.

### Updating Base Preview

12. Visit `https://dashboard.tugboat.qa/[project]/latest` to access the Base Preview.
13. Initialize the Base Preview, click Actions -> update.
14. Makefile -> tugboat-update is triggered.
15. Tugboat commits updated Base Preview.
16. Make manual changes to the Base Preview.
17. Realize that you made mistake.
18. Reset the Base Preview, click Actions -> reset.
19. Base preview is reverted to commit made in step 15

## Troubleshooting

* `Makefile:2: *** missing separator.  Stop.`

  This is caused by using spaces rather than tabs in your Makefile. If are using PHPStorm with Drupal Coding Standards configuration, you will need to overcome the automated usage of spaces by installing the [Editorconfig plugin](https://plugins.jetbrains.com/plugin/7294) and creating `.editorconfig` file with the following contents:

  ```
  # Tab indentation (no size specified)
  [Makefile]
  indent_style = tab
```
