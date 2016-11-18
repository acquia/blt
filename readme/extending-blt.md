# Extending / Overriding BLT

To add or override a Phing target, you may create a custom build file. You must specify the location of your custom build file using the `import` key to your project.yml file, e.g.:

    import: '${repo.root}/custom.xml'

## Adding a custom target

      <project name="custom" default="build">
        <!-- Add custom targets. -->
      </project>

## Overriding an existing target

To override an existing target, just give it the same name as the default target provided by BLT. E.g.,

      <project name="custom" default="build">
        <target name="local:update" description="Update current database to reflect the state of the Drupal file system; uses local drush alias.">
          <phingcall target="setup:update">
            <property name="drush.alias" value="${drush.aliases.local}"/>
          </phingcall>
        </target>
      </project>

## Overriding a variable value:

You can override the value of any Phing variable used by BLT by either:

1. Adding the variable to your project.yml file:

        behat.tags: @mytags

2. Specifying the variable value in your `blt` command using [Phing](https://www.phing.info/docs/stable/hlhtml/index.html#d5e792) argument syntax `-D[key]=[value]`, e.g.,

        blt tests:behat -Dbehat.tags='@mytags'

3. Using a custom build properties file rather than project.yml:

        blt tests:behat -propertyfile mycustomfile.yml -propertyfileoverride


## Disabling a target

You may disable any BLT target. This will cause the target to be skipped during the normal build process. To disable a target, add a `disable-targets` key to your project.yml file:

      disable-targets:
        validate:
          phpcs: true

This snippet would cause the `validate:phpcs` target to be skipped during BLT builds.

## Modifying BLT Configuration

BLT configuration can be customized by overriding the value of default variable values. You can find the default value of any BLT variable in [build.yml](https://github.com/acquia/blt/blob/8.x/phing/build.yml).

Listed below are some of the more commonly customized BLT targets.

### deploy:*

#### deploy:build

To modify the behavior of the `deploy:build` target, you may override BLT's `deploy` configuration:

       deploy:
         build-dependencies: true
         dir: ${repo.root}/deploy
         exclude_file: ${blt.root}/phing/files/deploy-exclude.txt
         gitignore_file: ${blt.root}/phing/files/.gitignore

More specifically, you can modify the build artifact in the following key ways:

1. Change which files are rsynced to the artifact by providing your own `deploy.exclude_file` value in project.yml. See [upstream deploy-exclude.txt](https://github.com/acquia/blt/blob/8.x/phing/files/deploy-exclude.txt) for example contents.  E.g.,

          deploy:
            exclude_file: ${repo.root}/blt/deploy/rsync-exclude.txt

1. Change which files are gitignored in the artifact by providing your own `deploy.gitignore_file` value in project.yml. See [upstream .gitignore](https://github.com/acquia/blt/blob/8.x/phing/files/.gitignore) for example contents. E.g.,

          deploy:
            gitignore_file: ${repo.root}/blt/deploy/.gitignore

1. Execute a custom command after the artifact by providing your own `target-hooks.post-deploy-build.dir` and `target-hooks.post-deploy-build.command` values in project.yml. E.g.,

          # Executed after deployment artifact is created.
          post-deploy-build:
            dir: ${deploy.dir}/docroot/profiles/contrib/lightning
            command: npm run install-libraries

### setup:*

#### setup:git-hooks

You may disable a git hook by setting its value under `git.hooks` to false:

        git:
          hooks:
            pre-commit: false

You may use a custom git hook in place of BLT's default git hooks by setting its value under `git.hooks` to the directory path containing of the hook. The directory must contain an executable file named after the git hook:

        git:
          hooks:
            pre-commit: ${repo.root}/my-custom-git-hooks

In this example, an executable file named `pre-commit` should exist in `${repo.root}/my-custom-git-hooks`.

### tests:*

#### tests:behat

To modify the behavior of the tests:behat target, you may override BLT's `behat` configuration:

        behat:
          config: ${repo.root}/tests/behat/local.yml
          profile: local
          # If true, `drush runserver` will be used for executing tests.
          run-server: false
          # This is used for ad-hoc creation of a server via `drush runserver`.
          server-url: http://127.0.0.1:8888
          # If true, PhantomJS GhostDriver will be launched with Behat.
          launch-phantom: true
          # An array of paths with behat tests that should be executed.
          paths:
            - ${docroot}/modules
            - ${docroot}/profiles
            - ${repo.root}/tests/behat
          tags: '~ajax'


### validate:*

#### validate:phpcs

To modify the behavior of the validate:phpcs target, you may override BLT's `phpcs` configuration:

        phpcs:
          filesets:
            - files.php.custom.modules
            - files.php.tests
            - files.frontend.custom.themes

The phpcs.filesets array contains references to Phing `<fileset>`s. You can remove or add your own custom filesets to the phpcs.filesets array.

The default filesets are defined in [filesets.xml](https://github.com/acquia/blt/blob/8.x/phing/tasks/filesets.xml).
