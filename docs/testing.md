# Testing

Software testing has been around for decades, and it has been proven to provide many crucial benefits, including:

* Reduce the number of bugs and regressions
* Increase project velocity (in the long run)
  * Improves accuracy of scheduling estimates
  * Saves time and money
* Increase user trust and satisfaction

You should use automated testing. Do not fall prey to common rationalizations and excuses relating to insufficient time, money, or resources. Time spent developing tests is repaid ten fold.

> Quality is the ally of schedule and cost, not their adversary. If we have to sacrifice quality to meet schedule, it’s because we are doing the job wrong from the very beginning.
>
> -- <cite>James A. Ward</cite>

> The bitterness of poor quality remains long after the sweetness of meeting the schedule has been forgotten.
>
> -- <cite>Karl Wiegers</cite>

That being said, two important pitfalls should be acknowledged:

1. It is possible to do automated testing incorrectly such that it is too expensive. See [Why Test Automation Costs Too Much](http://testobsessed.com/2010/07/why-test-automation-costs-too-much/).
2. It is possible to write automated tests that have little value.

To avoid these pitfalls, follow the best practices outlined in sections below.

### Resources

* [Realizing quality improvement through test driven development](http://research.microsoft.com/en-us/groups/ese/nagappan_tdd.pdf)
* [Why Test Automation Costs Too Much](http://testobsessed.com/2010/07/why-test-automation-costs-too-much/)

## Test directory structure

This directory contains all projects tests, grouped by testing technology. For all configuration related to builds that actually run these tests, please see the [build](/build) directory.

    tests
    ├── behat - contains all Behat tests
    │    ├── features
    │    │   ├── bootstrap
    │    │   └── Example.feature
    │    ├── behat.yml - contains behat configuration common to all behat profiles.
    │    └── integration.yml - contains behat configuration for the integration profile, which is used to run tests on the integration environment.
    ├── jmeter  - contains all jMeter tests
    └── phpunit - contains all PHP Unit tests

## Executing tests

Before attempting to execute any tests, verify that composer dependencies are built by running `composer install` in the project root.

The following testing commands are available:

* `blt tests:all`
* `blt tests:behat:run`
* `blt tests:phpunit:run`
* `blt tests:security:check:updates`

### Modifying test targets

See [Extending BLT](extending-blt.md#target-configuration) for more information on overriding default configuration values.

For more information on the commands, run:

* `./vendor/bin/phpunit --help`
* `./vendor/bin/behat --help`

## Behat

The high-level purpose BDD is to create a strong connection between business requirements and the actual tests. Behat tests should mirror ticket acceptance criteria as closely as possible.

Consequently, proper Behat tests should be written using business domain language. The test should be comprehensible by the stakeholder and represent a clear business value. It should represent a typical user behavior and need not be an exhaustive representation of all possible scenarios.

See referenced materials for more information on BDD best practices.

### Testing individual features or scenarios

To execute a single feature:

    blt tests:behat:run -D behat.paths=${PWD}/tests/behat/features/Examples.feature
    # Relative paths are assumed to be relative to tests/behat/features.
    blt tests:behat:run -D behat.paths=Examples.feature

To execute a single scenario:

    blt tests:behat:run -D behat.paths=${PWD}/tests/behat/features/Examples.feature:4
    # Relative paths are assumed to be relative to tests/behat/features.
    blt tests:behat:run -D behat.paths=Examples.feature:4

Where "4" is the line number of the scenario in the feature file.

To execute the tests directly (without BLT) see the following examples:

* `./vendor/bin/behat -c tests/behat/local.yml tests/behat/features/Examples.feature -p local`

### Configuration

Configuration for the BLT Behat commands is stored in the `behat` configuration variable. You can modify the behavior of the BLT `tests:behat:run` target by customizing this configuration. See [Extending BLT](extending-blt.md) for more information on overriding configuration variables.

Behat's own configuration is defined in the following files:

* tests/behat/behat.yml
* tests/behat/example.local.yml
* tests/behat/local.yml

#### Screenshots for failed steps

BLT includes the Behat [ScreenshotExtension](https://github.com/elvetemedve/behat-screenshot), configured by default to store a screenshot of any failed step locally. You can configure the extension globally under the `Bex\Behat\ScreenshotExtension` key in `tests/behat/behat.yml`, or override locally inside `tests/behat.local.yml`.

Read through the [ScreenshotExtension documentation](https://github.com/elvetemedve/behat-screenshot#configuration) to discover how to change where images are saved, disable the extension, or change the screenshot taking mode.

### Best practices

* Behat tests must be used behaviorally, i.e., they must use business domain language.
* Each test should be isolated, i.e., it should not depend on conditions created by another test. In pratice, this means:
    * Resetting testing environment via CI after test suite runs
    * Defining explicit cleanup tasks in features
* @todo add examples of good and bad features

### Common mistakes

* Writing Behat tests that do not use business domain language.
* Tests are not sufficiently isolated. Making tests interdependent diminishes their value!
* Writing tests that are exhaustive of all scenarios rather than representative of a typical scenario.
* Writing Behat tests when a unit test should be employed.

### Resources

* [Cucumber - Where to start?](https://github.com/cucumber/cucumber/wiki/Cucumber-Backgrounder#where-to-start)
Note that Cucumber is simply a Ruby based BDD library, whereas Behat is a
PHP based BDD library. Best practices for tests writing apply to both.
* [The training wheels came off](http://aslakhellesoy.com/post/11055981222/the-training-wheels-came-off)

## PHPUnit

Project level, functional PHPUnit tests are included in `tests/phpunit`. Any PHPUnit tests that affect specific modules or application level features should be placed in the same directory as that module, not in this directory.

### Best practices

* Tests should not contain any control statements
* Be careful to make both positive and negative assertions of expectations
* @todo add examples of good and bad tests

### Common mistakes

* Writing unit tests that are not independent
* Making unit tests too large. Tests should be small and granular.
* Asserting only positive conditions. Negative assertions should also be made.

### Resources

* [Drupal's implementation of PHPUnit](https://www.drupal.org/phpunit)
* [Presentations on PHPUnit](https://phpunit.de/presentations.html)
* [Test Driven Development: By Example (book)](http://www.amazon.com/dp/0321146530)
* [xUnit Test Patterns: Refactoring Test Code (book for the really serious)](http://amazon.com/dp/0131495054)
* [Unit testing: Why bother?](http://soundsoftware.ac.uk/unit-testing-why-bother/)

### Configuration

You can customize the `tests:phpunit:run` command by [customize the configuration values](extending-blt.md#modifying-blt-configuration) for the `phpunit` key.

Each row under the `phpunit` key should contain a combination of the following properties:

 * config: path to either the Core phpunit configuration file (docroot/core/phpunit.xml.dist) or a custom one. If left blank, no configuration will be loaded with the unit test.
 * path: the path to the custom phpunit test
 * class: the class name for the test
 * file: the sourcefile that declares the class provided in `class`
 * testsuite: run tests that are part of a specific `@testsuite`
 * group: run tests only tagged with a specific `@group`
 * exclude: run tests excluding any tagged with this `@group`
 * filter: allows text filter for tests

 See PHPUnit's [documentation](https://phpunit.de/manual/current/en/textui.htm) for additional information.

```yml
phpunit:
  - path: '${repo.root}/tests/phpunit'
    class: 'ExampleTest'
    file: 'ExampleTest.php'
  -
    config: ${docroot}/core/phpunit.xml.dist
    group: 'example'
    class: null
    file: null
  -
    config: ${docroot}/core/phpunit.xml.dist
    exclude: 'mylongtest'
    group: 'example'
    class: null
    file: null
  -
    config: ${docroot}/core/phpunit.xml
    path: '${docroot}/core'
    testsuite: 'functional'
    class: null
    file: null
   -
    config: ${docroot}/core/phpunit.xml
    path: ${docroot}/modules/custom/my_module
    class: ExampeleTest
    file: tests/src/Unit/ExampeleTest.php
```

## Frontend Testing

BLT supports a `frontend-test` target that can be used to execute a variety of testing frameworks. Examples may include Jest, Jasmine, Mocha, Chai, etc.

### Configuration

You can [customize the configuration values](extending-blt.md#modifying-blt-configuration) for the `frontend-test` key to enable this capability of BLT.
