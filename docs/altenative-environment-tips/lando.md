# Setting up BLT with Lando

## The good news:
BLT with Lando _mostly_ just works™.

## The bad news:
There are a couple tricky bits that you need to watch out for.

### Setting up behat tests

#### Missing Chrome driver
When running `lando blt tests:behat`, depending on your Lando recipe you may get an error about a missing Chrome driver. If that happens, you'll need to add the driver installation to your Lando recipe.

#### Chrome timeouts
If you get timeout errors, try running the blt command with the -vvv option to get more output: `lando blt -vvv tests:behat`. If you get errors like the ones below then the issue is with the permissions on your container.

```
[Filesystem\FilesystemStack] mkdir ["/app/reports"]
[info] Killing running google-chrome processes...
[info] Killing all processes on port '9222'...
[info] Launching headless chrome...
[Robo\Common\ProcessExecutor] Running 'google-chrome' --headless --disable-web-security --remote-debugging-port=9222  http://localhost in /app
[info] Waiting for response from http://localhost:9222...
[debug] cURL error 7: Failed to connect to localhost port 9222: Connection refused (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)
...

[info] Killing running google-chrome processes...
[info] Killing all processes on port '9222'...
[error]  Timed out.
12.412s total time elapsed.
```

If you ssh into lando and run the google-chrome command directly, you'll see:
```    
$ google-chrome --headless --disable-web-security --remote-debugging-port=9222  http://localhost
Failed to move to new namespace: PID namespaces supported, Network namespace supported, but failed: errno = Operation not permitted
Failed to generate minidump.Illegal instruction
 ```

The solution to this is to invoke the chrome command with the ` --no-sandbox` option. To do that, you'll need to patch your BLT installation to add that option to the [launchChrome() function in the Behat command](https://github.com/acquia/blt/blob/9.x/src/Robo/Commands/Tests/BehatCommand.php#L178).
See [the Patches documentation](patches.md) for tips on applying patches to packages via Composer.
