## Cloud Hooks

This directory contains [Acquia Cloud hooks](https://docs.acquia.com/cloud/manage/cloud-hooks).

### Directory Structure

    tests
    ├── common    - contains hooks that will be executed for _all_ environments.
    ├── dev       - contains hooks that will be executed for _dev_ environment.
    ├── prod      - contains hooks that will be executed for _prod_ environment.
    ├── samples   - contains example Cloud Hooks.
    ├── templates - contains templates, which may be cloned and used as a starting point for creating your own custom cloud hook scripts.
    └── test      - contains hooks that will be executed for _test_ environment.

### Documentation

For detailed information on how to implement these hooks, read the [documentation on Acquia.com](https://docs.acquia.com/cloud/manage/cloud-hooks)or visit the [Cloud Hook repository](https://github.com/acquia/cloud-hooks).
