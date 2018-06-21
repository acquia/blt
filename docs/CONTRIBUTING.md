# Support

If you experience issues with a local BLT build, try using Dr. BLT to diagnose your problem:

    blt doctor

If that isn't helpful, please post an issue on the [GitHub issue queue](https://github.com/acquia/blt/issues) including the following information:

- Your version of BLT, `composer info acquia/blt`
- Your operating system
- The **full** log output of your BLT command, wrapped in a [codeblock](https://help.github.com/articles/basic-writing-and-formatting-syntax/#quoting-code).

In seeking help, please keep the following points in mind:

* BLT is distributed under the GPLv2 license; WITHOUT ANY WARRANTY.
* The project maintainers are under no obligation to respond to support requests, feature requests, or pull requests.
* All contributions to BLT will be reviewed for compliance with Drupal Coding Standards and best practices as defined by the project maintainer.

# Contributing to BLT

BLT work is currently being tracked in the [BLT GitHub issue queue](https://github.com/acquia/blt/issues) and organized via a [Waffle.io Kanban Board](https://waffle.io/acquia/blt).

Please note the branch statuses documented in the README and [Github page](https://github.com/acquia/blt):
- Pull requests for enhancements will only be accepted for the active development branch.
- Pull requests for bug fixes will only be accepted for supported branches.

## Developing BLT locally

If you'd like to contribute by actively developing BLT, we suggest that you clone BLT and also created a BLT-ed project for testing your changes.

Use the following commands to create a testable BLT-created project alongside BLT

```
git clone https://github.com/acquia/blt.git
rm -rf blted8
composer install --working-dir=blt
cd blt
./vendor/bin/robo create:from-symlink
```

The new `blted8` directory will have a composer dependency on your local clone of BLT via a `../blt` symlink. You can therefore make changes to files in `blt` and see them immediately reflected in `blted8/vendor/acquia/blt`.

# Testing

To execute the same "release" testing that is performed during CI execution, run:

```
./vendor/bin/robo release:test
```

## PHPUnit

See [the PHPUnit section in the automated testing docs](testing.md#PHPUnit)
