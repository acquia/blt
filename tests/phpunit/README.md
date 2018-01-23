# BLT Internal Testing Framework

## Execute PHP Unit tests

```
./vendor/bin/phpunit
```

## Customize PHPUnit execution

PHPUnit can be configured either through a configuration file or through command line options. Depending on how often you rerun tests, one may be an easier approach than another.

### XML configuration
```
cp phpunit.xml.dist phpunit.xml
```

Edit phpunit.xml.

### CLI options

```
./vendor/bin/phpunit -my-options
```

For example, to exclude certain groups of tests,

```
./vendor/bin/phpunit --exclude-group requires-db
```

## Sandbox

To test BLT, we must first create a test project that uses BLT. To avoid doing this for every unit test, the testing framework creates a "master sandbox" once during PHPUnit bootstrap. It then uses that master as a reference to create new sandbox instances for each unit test.

If you are testing locally, you may want to prevent the "master sandbox" from being regenerated during each PHPUnit bootstrap. This allows you to test and re-test much faster. To do this:

Disable `BLT_RECREATE_SANDBOX_MASTER`:
```
    <env name="BLT_RECREATE_SANDBOX_MASTER" value="0"/>
```

Alternately, you may set this environmental value during command execution:
```
BLT_RECREATE_SANDBOX_MASTER=0 ./vendor/bin/phpunit
```
