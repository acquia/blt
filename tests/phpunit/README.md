# BLT Internal Testing Framework

## Execute PHP Unit tests

```
./vendor/bin/phpunit
```

## Customize PHPUnit execution

```
cp phpunit.xml.dist phpunit.xml
```

Edit phpunit.xml.

## Sandbox

To test BLT, we must first create a test project that uses BLT. To avoid doing this for every unit test, the testing framework creates a "master sandbox" once during PHPUnit bootstrap. It then uses that master as a reference to create new sandbox instances for each unit test.

If you are testing locally, you may want to prevent the "master sandbox" from being regenerated during each PHPUnit bootstrap. This allows you to test an re-test much faster. To do this:

Disable `BLT_RECREATE_SANDBOX_MASTER`:
```
    <env name="BLT_RECREATE_SANDBOX_MASTER" value="0"/>
```

Alternately, you may set this environmental value during command execution:
```
BLT_RECREATE_SANDBOX_MASTER=0 ./vendor/bin/phpunit
```

## Verbosity

By default, the testing framework does not print the output of the commands that it is testing. Doing so would be too verbose. To force the output to be printed:

```
    <env name="BLT_PRINT_COMMAND_OUTPUT" value="1"/>
```

Alternately, you may set this environmental value during command execution:
```
BLT_PRINT_COMMAND_OUTPUT=1 ./vendor/bin/phpunit
