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

#### ACSF: Undefined index notices for $_SERVER keys

*Unresolved*

When running many BLT or drush commands through Lando when ACSF has been initialized, notices like the one below appear. It appears the `$_SERVER['HTTP_HOST]` and `$_SERVER['argv']` variables are not being populated in Lando. Beyond warnings/notices displayed to the screen, it's unclear what the impact of this is.

**example output**
```
<em class="placeholder">Notice</em>: Undefined index: HTTP_HOST in <em class="placeholder">require()</em> (line <em class="placeholder">119</em> of <em class="placeholder">/app/vendor/acquia/blt/settings/blt.settings.php</em>). <pre class="backtrace">require(&#039;/app/vendor/acquia/blt/settings/blt.settings.php&#039;) (Line: 797)
require(&#039;/app/docroot/sites/default/settings.php&#039;) (Line: 122)
Drupal\Core\Site\Settings::initialize(&#039;/app/docroot&#039;, &#039;sites/default&#039;, Object) (Line: 1056)
Drupal\Core\DrupalKernel-&gt;initializeSettings(Object) (Line: 271)
Drupal\Core\DrupalKernel::createFromRequest(Object, Object, &#039;prod&#039;, 1) (Line: 172)
Drush\Boot\DrupalBoot8-&gt;bootstrapDrupalConfiguration(NULL) (Line: 295)
Drush\Boot\BootstrapManager-&gt;doBootstrap(3, 6, NULL) (Line: 504)
Drush\Boot\BootstrapManager-&gt;bootstrapMax() (Line: 224)
Drush\Application-&gt;bootstrapAndFind(&#039;csex&#039;) (Line: 191)
Drush\Application-&gt;find(&#039;csex&#039;) (Line: 229)
Symfony\Component\Console\Application-&gt;doRun(Object, Object) (Line: 148)
Symfony\Component\Console\Application-&gt;run(Object, Object) (Line: 112)
Drush\Runtime\Runtime-&gt;doRun(Array) (Line: 41)
Drush\Runtime\Runtime-&gt;run(Array) (Line: 66)
require(&#039;/app/vendor/drush/drush/drush.php&#039;) (Line: 17)
drush_main() (Line: 141)
require(&#039;phar:///usr/local/bin/drush/bin/drush.php&#039;) (Line: 10)
</pre>
```

```
Notice: Undefined index: argv in Symfony\Component\Console\Input\ArgvInput->__construct() (line 53 of /app/vendor/symfony/console/Input/ArgvInput.php).

Symfony\Component\Console\Input\ArgvInput->__construct(NULL) (Line: 113)
require('/app/vendor/acquia/blt/settings/blt.settings.php') (Line: 797)
require('/app/docroot/sites/default/settings.php') (Line: 122)
Drupal\Core\Site\Settings::initialize('/app/docroot', 'sites/default', Object) (Line: 1056)
Drupal\Core\DrupalKernel->initializeSettings(Object) (Line: 656)
Drupal\Core\DrupalKernel->handle(Object) (Line: 19)

```
