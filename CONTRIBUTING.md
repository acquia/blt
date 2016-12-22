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

## Developing BLT locally

If you'd like to contribute by actively developing BLT, we suggest that you clone BLT and also created a BLT-ed project for testing your changes.

Use the following commands to create a testable BLT-created project alongside BLT

```
git clone https://github.com/acquia/blt.git
rm -rf blted8
composer install --working-dir=blt
cp -R blt/blt-project blted8
cd blted8
git init
composer install
./vendor/bin/blt install-alias
rm -rf vendor
composer update
git add -A
git commit -m 'Initial commit.'
```

The new `blt-project` directory will have a composer dependency on your local clone of BLT via a `../blt` symlink. You can therefore make changes to files in `blt` and see them immediately reflected in `blt-project/vendor/acquia/blt`.

## Development conventions

### Phing targets vs. Symfony commands?

While Phing and the Symfony Console can both accomplish some of the same tasks, they are different tools with different intended purposes. When developing functionality for BLT we are careful to choose the right tool for the right job.

Phing is intended to be a build tool. It is particularly good at stringing together multiple commands and tasks into a single target which can then be executed procedurally. We use Phing when are requirements are well suited to this strength.

The commands that Phing executes can, of course, be provided by anything. Some are native linux commands, some are provided by tools like Composer and NPM, while others may be provided by the Symfony Console component.

As a rule, we _use Symfony console to provide fixed-scope commands_. These commands should be flexible and have absolutely no intrinsic awareness of the greater build process. We _use Phing to call commands within the context of a build process_, executing them with specific argument values at the correct time.

