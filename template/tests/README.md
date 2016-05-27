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
* [Why Test Automation Costs Too Much](http://testobsessed.com/2010/07/why-test-automation-costs-too-much/).

## Types of testing

You should use the following types of automated tests:

* [Code sniffing](https://www.drupal.org/project/coder)
* Code linting
* Functional tests
* Unit tests

## Functional testing

Functional tests should be written with [Behat](http://docs.behat.org/en/v2.5/) using a [Behavior Driven Development](http://dannorth.net/introducing-bdd/) methodology.

See [Behat section](#behat) below for more details.

## Unit testing

Unit testing is the practice of testing the components of a program automatically, using a test program to provide inputs to each component and check the outputs.  They are meant to be highly granular and completely independent. Running units tests should be a very fast process.

PHPUnit should be used for unit testing. See [PHPUnit section](#phpunit) below for more details

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

## <a name="executing-tests"></a>Executing tests

Before attempting to execute any tests, verify that composer dependencies are built by running `composer install` in the project root.

Each testing type can be either executed directly, or via a corresponding Phing target. Phing will execute the tests with default values defined in your project's yaml configuration files (project.yml). Examples:

* `./blt.sh tests:all`
* `./blt.sh tests:behat`
* `./blt.sh tests:phpunit`

To execute the tests directly (without Phing) see the following examples:

* `./vendor/bin/behat -c tests/behat/local.yml tests/behat/features/Examples.feature`
* `./vendor/bin/phpunit tests/phpunit/BLTTest.php`

For more information on the commands, run:

* `./vendor/bin/phpunit --help`
* `./vendor/bin/behat --help`

## <a name="behat"></a>Behat

The high-level purpose BDD is to create a strong connection between business requirements and the actual tests. Behat tests should mirror ticket acceptance criteria as closely as possible.

Consequently, proper Behat tests should be written using business domain language. The test should be comprehensible by the stakeholder and represent a clear business value. It should represent a typical user behavior and need not be an exhaustive representation of all possible scenarios. 

See referenced materials for more information on BDD best practices. 

### Best practices:

* Behat tests must be used behaviorally. I.E., they must use business domain language.
* Each test should be isolated. E.g., it should not depend on conditions created by another test. In pratice, this means:
    * Resetting testing environment via CI after test suite runs
    * Defining explicit cleanup tasks in features
* @todo add examples of good and bad features

### Common mistakes

* Writing Behat tests that do not use business domain language.
* Tests are not sufficiently isolated. Making tests interdependent diminishes their value!
* Writing tests that are exhaustive of all scenarios rather than representative of a typical scenario.
* Writing Behat tests when a unit test should be employed.

### Resources:

* [Cucumber - Where to start?](https://github.com/cucumber/cucumber/wiki/Cucumber-Backgrounder#where-to-start) 
Note that Cucumber is simply a Ruby based BDD library, whereas Behat is a 
PHP based BDD library. Best practices for tests writing apply to both
* [The training wheels came off](http://aslakhellesoy.com/post/11055981222/the-training-wheels-came-off)


## <a name="phpunit"></a>PHPUnit

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
