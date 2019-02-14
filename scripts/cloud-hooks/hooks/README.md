## What are Cloud Hooks?

Cloud Hooks is a feature of Acquia Cloud, the Drupal cloud hosting platform.

A Cloud Hook is simply a script in your code repository that Acquia Cloud executes on your behalf when a triggering action occurs. Examples of tasks that you can automate with Cloud Hooks include:

* "Scrub" your Production database when it is copied to Dev or Staging by removing customer emails or disabling production-only modules.

Please note: even though Acquia Cloud hooks exist in Acquia Cloud Site Factory, the use of most of them is unsupported in Acquia Cloud Site Factory because Acquia Cloud hooks are not designed for use with multisites. Hooks other than the ones documented below are not applicable for Acquia Cloud Site Factory.

Acquia Cloud Site Factory hooks exist as an addition to Acquia Cloud hooks and work differently; see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks for more information.

## Installing Cloud Hooks

Cloud hook scripts live in your Acquia Cloud code repository. In each branch of your repo, there is a directory named docroot that contains your site's source code. Cloud hooks live in the directory hooks NEXT TO docroot (not inside of docroot).

To install the correct directory structure and sample hook scripts, simply copy this repo into your Acquia Cloud repo.

If you are using Git:

    cd /my/repo
    curl -L -o hooks.tar.gz https://github.com/acquia/cloud-hooks/tarball/master
    tar xzf hooks.tar.gz
    mv acquia-cloud-hooks-* hooks
    git add hooks
    git commit -m 'Import Cloud hooks directory and sample scripts.'
    git push

If you are using SVN:

    cd /my/repo
    curl -L -o hooks.tar.gz https://github.com/acquia/cloud-hooks/tarball/master
    tar xzf hooks.tar.gz
    mv acquia-cloud-hooks-* hooks
    svn add hooks
    svn commit -m 'Import Cloud hooks directory and sample scripts.'

## The Cloud Hooks directory

The hooks directory in your repo has a directory structure like this:

    /hooks / [env] / [hook] / [script]

* [env] is a directory whose name is an environment name: 'dev' for Development, 'test' for Staging, and 'prod' for Production, as well as 'common' for all environments.

* [hook] is a directory whose name is a Cloud Hook name: see below for supported hooks.

* [script] is a program or shell script within the [env]/[hook] directory.

Each time a hookable action occurs, Acquia Cloud runs scripts from the directory common/[hook] and [target-env]/[hook]. All scripts in the hook directory are run, in lexicographical (shell glob) order. If one of the hook scripts exits with non-zero status, the remaining hook scripts are skipped, and the task is marked "failed" so you know to check it. All stdout and stderr output from all the hooks that ran are displayed in the task log.

Note that hook scripts must have the Unix "executable" bit in order to run. If your script has the execute bit set when you first add it to Git or SVN, you're all set. Otherwise, to set the execute bit to a file already in your Git repo:

    chmod a+x ./my-hook.sh
    git add ./my-hook.sh
    git commit -m 'Add execute bit to my-hook.sh'
    git push

If you are using SVN:

    chmod a+x ./my-hook.sh
    svn propset svn:executable ON ./my-hook.sh
    svn commit -m 'Add execute bit to my-hook.sh'


## Supported hooks

This section defines the Cloud Hooks that are supported on Acquia Cloud Site Factory in practice and the command-line arguments they receive.

### post-db-copy

The post-db-copy hook is run whenever a database gets copied from one environment to another. That is: when a website is staged.

Please note the following:
* There is already at least one hook script that gets executed: the standard 000-acquia_required_scrub.php that is installed as part of the acsf module.
* This script executes drush sql-sanitize. For scrubbing data in your database, hooking into the execution of the sql-sanitize command is often preferred to having a separate hook script.

Usage: post-db-copy site target-env db-name source-env

* site: The site name. This is the same as the Acquia Cloud username for the site.
* target-env: The environment to which the database was copied.
* db-name: The name of the database that was copied. See below.
* source-env: The environment from which the database was copied.

db-name is not the actual MySQL database name but rather the common name for the database in all environments. Use the drush ah-sql-cli  to connect to the actual MySQL database, or use th drush ah-sql-connect command to convert the site name and target environment into the specific MySQL database name and credentials. (The drush sql-cli and sql-connect commands work too, but only if your Drupal installation is set up correctly.)

Example: To "scrub" your production database by removing all user accounts every time it is copied into your Stage environment, put this script into /hooks/test/post-db-copy/delete-users.sh:

    #!/bin/bash
    site=$1
    env=$2
    db=$3
    echo "DELETE FROM users" | drush @$site.$env ah-sql-cli --db=$db

For a more elaborate example of calling a drush command, see cloud-hooks/samples/acquia-cloud-site-factory-post-db.sh
