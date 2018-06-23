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
