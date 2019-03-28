# Contributing to BLT

BLT feature requests, bugs, support requests, and milestones are tracked via the [BLT GitHub issue queue](https://github.com/acquia/blt/issues).

Before submitting an issue or pull request, please read and take the time to understand this guide. Issues not adhering to these guidelines may be closed.

## Submitting issues

Please choose your issue type carefully. If you aren't sure, odds are you have a support request.
- **Feature request**: a request for a specific enhancement to be made to BLT. This is distinct from a bug in that it represents a _gap_ in BLT functionality, rather than an instance of BLT behaving badly. It is distinct from a support request in that it has a specific and atomic request for new BLT functionality, rather than being a general request for help or guidance.
- **Bug report**: a clearly defined instance of BLT not behaving as expected. It is distinct from a feature request in that it represents a mismatch between what BLT _does_ and what BLT _claims to do_. It is distinct from a support request in that it has _specific steps to reproduce the problem_ (ideally starting from a fresh installation of BLT) and _justification_ as to why this is a problem with BLT rather than an underlying tool such as Composer or Drush.
- **Support request**: a request for help or guidance. Use this if you aren't sure how to do something or can't find a solution to a problem that may or may not be a bug. Before filing a support request, please review the FAQ for solutions to common problems and general troubleshooting techniques. If you have an Acquia subscription, consider filing a Support ticket instead of a BLT issue in order to receive support subject to your SLA.

After you have chosen your issue type, make sure to fill out the issue template completely.

Newly-filed issues will be triaged by a BLT maintainer. If additional information is requested and no reply is received within a week, issues may be closed.

Note the following when submitting issues:
* Issues filed directly to the BLT project are not subject to an SLA.
* BLT is distributed under the GPLv2 license; all documentation, code, and guidance is provided without warranty.
* The project maintainers are under no obligation to respond to support requests, feature requests, or pull requests.


## Submitting pull requests

Please note the branch statuses documented in the README and [GitHub page](https://github.com/acquia/blt):
- Pull requests for enhancements will only be accepted for the active development branch.
- Pull requests for bug fixes will only be accepted for supported branches.
- When submitting a pull request for a bug fix or enhancement that may apply to multiple branches, please submit only a single PR to the latest development branch for review. A maintainer will backport the fix if appropriate.

Pull requests must also adhere to the following guidelines:
- PRs should be atomic and targeted at a single issue rather than broad-scope.
- PRs must contain clear testing steps and justification, as well as all other information required by the pull request template.
- PRs must pass automated tests before they will be reviewed. We recommend you run the tests locally before submitting (see below).
- PRs must comply with Drupal coding standards and best practices as defined by the project maintainers.

Pull requests will be reviewed by a BLT maintainer and are not subject to an SLA. If additional information or work is requested and no reply is received within a week, PRs may be closed.

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

Note that this requires the following:
- Four local MySQL databases available with drupal, drupal2, drupal3, and drupal4 as the db names
- A MySQL user with access to the above, with drupal as the username and password. It may be sensitive to MySQL version. In newer versions of MySQL (8+), you may need to set the user password like so: `alter user 'drupal'@'localhost' identified with mysql_native_password by 'drupal';`.
- The PHP MySQL extension to be enabled.
- Chromedriver, sqlite, and the php-sqlite3 extension in order to run `@group drupal` tests.
- You may want to exclude `@group requires-vm`.

## PHPUnit

See [the PHPUnit section in the automated testing docs](testing.md#PHPUnit)
