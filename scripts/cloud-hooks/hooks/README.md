## Cloud Hooks

This directory contains [Acquia Cloud hooks](https://docs.acquia.com/cloud/manage/cloud-hooks).

### Directory Structure

    tests
    ├── common    - contains hooks that will be executed for _all_ environments.
    ├── dev       - contains hooks that will be executed for _dev_ environment.
    ├── prod      - contains hooks that will be executed for _prod_ environment.
    └── test      - contains hooks that will be executed for _test_ environment.

### Default hooks

By default, BLT provides a post-code-update and post-code-deploy hook that runs the `artifact:update:drupal` target in all environments following a code deployment or update.

### Documentation and additional examples

For detailed information on how to implement these hooks, read the [documentation on Acquia.com](https://docs.acquia.com/cloud/manage/cloud-hooks) and find sample hooks in the [Cloud Hook repository](https://github.com/acquia/cloud-hooks).
