# Testing

Software testing has been around for decades, and it has been proven to provide many crucial benefits, including:

* Reducing the number of bugs and regressions
* Increasing project velocity (in the long run)
* Improving accuracy of scheduling estimates
* Saving time and money
* Increasing user trust and satisfaction

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

This directory contains all projects tests, grouped by testing technology. For all configuration related to builds that actually run these tests, please see the [blt](/blt) directory.

    tests
    ├── behat - contains all Behat tests
    │    ├── features
    │    │   ├── bootstrap
    │    │   └── Example.feature
    │    ├── behat.yml - contains behat configuration common to all behat profiles.
    │    └── integration.yml - contains behat configuration for the integration profile, which is used to run tests on the integration environment.
    └── phpunit - contains PHPUnit tests for the project (Drupal PHPUnit tests should reside within a given Drupal module).

Additional technologies (some of which may not be supported by BLT) can also have their tests bundled in the tests folder for convenience (e.g. `tests/jmeter`).
    
BLT also supports the bundling and execution of phpunit tests from locations outside of the tests folder. See the [PHPUnit Configuration](#configuration-1) section below for additional information. 

## Executing tests

Before attempting to execute any tests, verify that composer dependencies are built by running `composer install` in the project root.

The following testing commands are available:

* `blt tests:all`
* `blt tests:behat:run`
* `blt tests:phpunit:run`
* `blt tests:drupal:run`
* `blt tests:security:check:updates`
* `blt tests:security:check:composer`

### Modifying test targets

See [Extending BLT](extending-blt.md#target-configuration) for more information on overriding default configuration values.

For more information on the commands, run:

* `./vendor/bin/phpunit --help`
* `./vendor/bin/behat --help`

## Behat

The high-level purpose of BDD is to create a strong connection between business requirements and the actual tests. Behat tests should mirror ticket acceptance criteria as closely as possible.

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

### Troubleshooting

* Google Chrome is missing: BLT currently expects Google Chrome (more exactly, the `google-chrome` binary) to be available on the local setup. If you're using a Mac and Chrome is installed, you should be good to go. Similarly, BLT includes the Chrome binary in DrupalVM. If you are running BLT in another Linux environment, install `chromium-driver`.
* Spotting bad configuration: In order to troubleshoot your Behat setup, make sure to run `blt doctor --site=mysite.local` to try and spot any obvious issue.
* Multisite / ACSF issues: When you need to run tests with any authenticated user role, you may have to uninstall `simplesamlphp_auth`. Else, Behat tests may hang. When ready, run `blt behat --site=mysite.local` from within the project root. If running Behat tests fail, then it means you either have issues with your BLT / Behat setup or there's an issue with tests themselves.

### Resources

* [Cucumber - Where to start?](https://github.com/cucumber/cucumber/wiki/Cucumber-Backgrounder#where-to-start)
Note that Cucumber is simply a Ruby based BDD library, whereas Behat is a
PHP based BDD library. Best practices for tests writing apply to both.
* [The training wheels came off](http://aslakhellesoy.com/post/11055981222/the-training-wheels-came-off)

## Unit and functional testing

### Best practices

* Tests should not contain any control statements.
* Be careful to make both positive and negative assertions of expectations.
* @todo add examples of good and bad tests.

### Common mistakes

* Writing unit tests that are not independent.
* Making unit tests too large. Tests should be small and granular.
* Asserting only positive conditions. Negative assertions should also be made.

### Resources

* [Drupal's implementation of PHPUnit](https://www.drupal.org/phpunit)
* [Presentations on PHPUnit](https://phpunit.de/presentations.html)
* [Test Driven Development: By Example (book)](http://www.amazon.com/dp/0321146530)
* [xUnit Test Patterns: Refactoring Test Code (book for the really serious)](http://amazon.com/dp/0131495054)
* [Unit testing: Why bother?](http://soundsoftware.ac.uk/unit-testing-why-bother/)

### Configuration

The `tests` configuration variable has following properties:

 * `reports.localDir`: Directory used to save testing reports on local environments
 * `reports.remoteDir`: Directory used to save testing reports on remote environments
 * `run-server`: Whether or not to launch the Drush server for testing
 * `server.port`: The Drush run-server port, default is `8888`
 * `server.url`: The URL for Drush server, default is `http://127.0.0.1:8888`
 * `selenium.port`: Port for Selenium, default is `4444`
 * `selenium.url`: URL for Selenium, default is `http://127.0.0.1:4444/wd/hub`
 * `chrome.port`: Port for `chrome` browser, default is `9222`
 * `chrome.args`: Args for `chrome` browser, default is `null`
 * `chromedriver.port`: Port for `chromedriver` WebDriver for Chrome, default is `9515`
 * `chromedriver.args`: Args for `chromedriver` WebDriver for Chrome, default is `null`

## PHPUnit

Project level, functional PHPUnit tests are included in `tests/phpunit`. Any PHPUnit tests that affect specific modules or application level features should be placed in the same directory as that module or feature code, not in this directory.

You can customize the `tests:phpunit:run` command by [modifying BLT Configuration](extending-blt.md#modifying-blt-configuration) for the `tests:phpunit` key.

Each row under the `tests:phpunit` key should contain a combination of the following properties:

 * `bootstrap`: A "bootstrap" PHP file that is run before the tests
 * `class`: the class name for the test
 * `config`: path to either the Core phpunit configuration file (docroot/core/phpunit.xml.dist) or a custom one. If left blank, no configuration will be loaded with the unit test.
 * `debug`: if `true`, will display debugging information
 * `directory`: directory to scan for tests
 * `exclude`: run tests excluding any tagged with this `@group`
 * `file`: the sourcefile that declares the class provided in `class`
 * `filter`: allows text filter for tests
 * `group`: run tests only tagged with a specific `@group`
 * `path`: the directory where the phpunit command will be run from
 * `printer`: the TestListener implementation to use
 * `stop-on-error`: if `true`, will stop execution upon first error
 * `stop-on-failure`: if `true`, sill stop execution upon first error or failure
 * `testdox`: if `true`, report test execution progress in TestDox format
 * `testsuite`: run tests that are part of a specific `@testsuite`
 * `testsuites`: (array) run tests from multiple `@testsuite`s (takes precedence over `testsuite`)

 See PHPUnit's [documentation](https://phpunit.de/manual/current/en/textui.htm) for additional information.

```yml
tests:
  phpunit:
    - # Run BLT"s example test.
      path: '${repo.root}/tests/phpunit'
      config: '${docroot}/core/phpunit.xml.dist'
      class: 'ExampleTest'
      file: 'ExampleTest.php'
```

## Testing Drupal with PHPUnit

Each row under the `tests:drupal` key should contain a combination of the following properties (see Drupal's `core/phpunit.xml.dist` for additional details):

 * `test-runner`: Whether to run Drupal tests with PHPUnit (`phpunit`) or Drupal's run-tests.sh script (`run-tests-script`)
 * `sudo-run-tests`: Whether or not to use sudo when running Drupal tests
 * `web-driver`: WebDriver to use for running Drupal's functional JavaScript tests (only `chromedriver` is supported at this time)
 * `browsertest-output-directory`: Directory to write output for browser tests (value for `BROWSERTEST_OUTPUT_DIRECTORY`)
 * `apache-run-group`: Unix user used for tests (value for `APACHE_RUN_USER`)
 * `apache-run-user`: Unix group used for tests (value for `APACHE_RUN_GROUP`)  (if `sudo-run-tests:true`, this is used to run testing commands as `sudo -u www-data -E ./vendor/bin/phpunit {...}`)
 * `mink-driver-args`: Driver args to mink tests (value for `MINK_DRIVER_ARGS`)
 * `mink-driver-args-webdriver`: Driver args to webdriver tests (value for `MINK_DRIVER_ARGS_WEBDRIVER`)
 * `mink-driver-class`: Driver class for mink tests (value for `MINK_DRIVER_CLASS`)
 * `simpletest-base-url`: Base URL for Simpletest (value for `SIMPLETEST_BASE_URL`)
 * `simpletest-db`: Connection string Simpletest database (value for `for SIMPLETEST_DB`)
 * `symfony-deprecations-helper`: Setting to `disabled` disables deprecation testing completely (value for `SYMFONY_DEPRECATIONS_HELPER`)
 * `phpunit`: Tests to run using Drupal's implementation of PHPUnit. This requires Drupal to be installed.
 * `drupal-tests`: Tests to run with Drupal's run-test.sh script.

```yml
tests:
  drupal:
    phpunit:
      - # Run Drupal' unit, kernel, functional, and functional-javascript testsuites for the action module.
        path: '${docroot}/core'
        config: ${docroot}/core/phpunit.xml.dist
        testsuites:
          - 'unit'
          - 'kernel'
          - 'functional'
          - `functional-javascript`
        group: action
      - # Run all tests in the custom modules directory.
        path: '${docroot}/core'
        config: ${docroot}/core/phpunit.xml.dist
        directory: ${docroot}/modules/custom
```

Note that Selenium is required to run Drupal tests, and Acquia Pipelines does not support Selenium (since it does not have Java installed). Thus, you cannot run these tests on Pipelines. You can still run the rest of the test suite (Behat, PHPUnit, etc...) using Chrome.

### Drupal's `run-tests.sh` script

You can customize the `tests:drupal:run` command by [modifying BLT Configuration](extending-blt.md#modifying-blt-configuration) for the `tests:run-tests` key.

Each row under the `tests:drupal-tests` key should contain a combination of the below properties. See Drupal's [documentation](https://www.drupal.org/docs/8/phpunit/running-tests-through-command-line-with-run-testssh) for a description of each properties.

 * `all`
 * `browser`
 * `clean`
 * `color`
 * `concurrency`
 * `dburl`
 * `die-on-fail`
 * `directory`
 * `keep-results-table`
 * `keep-results`
 * `repeat`
 * `sqlite`
 * `suppress-deprecations`
 * `tests` (array)
 * `types` (array, takes precedence over `type`)
 * `type`
 * `url`

```yml
tests:
  drupal:
    drupal-tests:
      - # Run the PHPUnit-Unit, PHPUnit-Kernel, and PHPUnit-Functional test types for the action module.
        color: true
        concurrency: 2
        types:
          - 'PHPUnit-Unit'
          - 'PHPUnit-Kernel'
          - 'PHPUnit-Functional'
        tests:
          - 'action'
        sqlite: '${tests.drupal.sqlite}'
        url: '${tests.drupal.simpletest-base-url}'
      - # Run the PHPUnit-FunctionalJavascript test type for the action module.
        color: true
        concurrency: 1
        types:
          - 'PHPUnit-FunctionalJavascript'
        tests:
          - 'action'
        sqlite: '${tests.drupal.sqlite}'
        url: '${tests.drupal.simpletest-base-url}'
      - # Run the Simpletest test type for the user module.
        color: true
        concurrency: 1
        types:
          - 'Simpletest'
        tests:
          - 'user'
        sqlite: '${tests.drupal.sqlite}'
        url: '${tests.drupal.simpletest-base-url}'
```

## Frontend Testing

BLT supports a `frontend-test` target that can be used to execute a variety of testing frameworks. Examples may include Jest, Jasmine, Mocha, Chai, etc.

### Configuration

You can [customize the configuration values](extending-blt.md#modifying-blt-configuration) for the `frontend-test` key to enable this capability of BLT.
