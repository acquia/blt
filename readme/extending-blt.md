# Extending / Overriding BLT

BLT uses Robo to provide commands.

## Adding a custom Robo Command

To create your own Robo PHP command:

1. Create a new file in `blt/src/Commands` named using the pattern `*Command.php`. The file naming convention is required.
1. You must use the namespace `Acquia\Blt\Custom\Commands` in your command file.
1. Generate an example command file by executing `blt example:init`. You may use the generated file as a guide for writing your own command.
1. Follow the [Robo PHP Getting Started guide](http://robo.li/getting-started/#commands) to write a custom command.

## Adding a custom Robo Hook

BLT uses the [Annotated Command](https://github.com/consolidation/annotated-command) library to enable you to hook into BLT commands. This allows you to execute custom code
in response to various events, typically just before or just after a BLT command is executed.

To create a hook:

1. Create a new file in `blt/src/Hooks` named using the pattern `*Hook.php`.
1. Generate an example hook file by executing `blt example:init`. You may use the generated file as a guide for writing your own command.

For a list of all available hook types, see [Annotated Command's hook types](https://github.com/consolidation/annotated-command#hooks).

## Replacing/Overriding a Robo Command

To replace a BLT command with your own custom version, implement the [replace command annotation](https://github.com/consolidation/annotated-command#replace-command-hook) for your custom command.

Please note that when you do this, you take responsibility for maintaining your custom command. Your command may break when changes are made to the upstream version of the command in BLT itself.

## Disabling a command

You may disable any BLT command. This will cause the target to be skipped during the normal build process. To disable a target, add a `disable-targets` key to your project.yml file:

      disable-targets:
        validate:
          phpcs: true

This snippet would cause the `validate:phpcs` target to be skipped during BLT builds.

## Adding / overriding filesets

1. Generate an example `Filesets.php` file by executing `blt example:init`. You may use the generated file as a guide for writing your own filesite.
1. Create a public method in the `Filesets` class in the generated file.
1. Add a Fileset annotation to your public method, specifying its id:

        @fileset(id="files.php.custom.mytheme")

1. Instantiate and return a `Symfony\Component\Finder\Finder` object. The files found by the finder comprise the fileset.
1. You may use the Fileset id in various configuration values in your `blt/project.yml` file. E.g., modify `validate:phpcs` such that it scans only your custom fileset, you would add the following to `blt/project.yml`:

        phpcs:
          filesets:
            - files.php.custom.mytheme

## Modifying BLT Configuration

BLT configuration can be customized by overriding the value of default variable values. You can find the default value of any BLT variable in [build.yml](https://github.com/acquia/blt/blob/8.x/config/build.yml).

### Overriding a variable value:

Configuration values are loaded, in this order, from the following list of YAML files:

-  blt/project.yml
-  blt/[environment].yml
-  blt/project.local.yml

Values loaded from the later files will overwrite values in earlier files.

### Overriding project-wide

You can override any variable value by adding an entry for that variable to your `project.yml` file. This change will be committed to your repository and shared by all developers for the project. For example:

        behat.tags: @mytags

### Overriding locally

You can override a variable value for your local machine by adding an entry for that variable to your `project.local.yml file`.  This change will not be committed to your repository.

### Overriding in specific environments

You may override a variable value for specific environments, such as a the `ci` environment, by adding an entry for that variable to a file named in the pattern [environment].yml. For instance, ci.yml.

At present, only the CI environment is automatically detected.

### Overriding at runtime

You may overwrite a variable value at runtime by specifying the variable value in your `blt` command using argument syntax `-D [key]=[value]`, e.g.,

        blt tests:behat -D behat.tags='@mytags'

Listed below are some of the more commonly customized BLT targets.

### deploy:*

#### deploy:build

To modify the behavior of the `deploy:build` target, you may override BLT's `deploy` configuration:

      deploy:
        # If true, dependencies will be built during deploy. If false, you should commit dependencies directly.
        build-dependencies: true
        dir: ${repo.root}/deploy
        exclude_file: ${blt.root}/scripts/blt/scripts/deploy/deploy-exclude.txt
        exclude_additions_file: ${repo.root}/blt/deploy-exclude-additions.txt
        gitignore_file: ${blt.root}/blt/scripts/deploy/.gitignore
        git:
          # If true, deploys will fail if there are uncommitted changes.
          failOnDirty: true

More specifically, you can modify the build artifact in the following key ways:

1. Change which files are rsynced to the artifact by providing your own `deploy.exclude_file` value in project.yml. See [upstream deploy-exclude.txt](https://github.com/acquia/blt/blob/8.x/scripts/blt/deploy/deploy-exclude.txt) for example contents.  E.g.,

          deploy:
            exclude_file: ${repo.root}/blt/deploy/rsync-exclude.txt

1. If you'd simply like to add onto the [upstream deploy-exclude.txt](https://github.com/acquia/blt/blob/8.x/scripts/blt/deploy/deploy-exclude.txt) instead of overriding it, you need not define your own `deploy.exclude_file`. Instead, simply leverage the `deploy-exclude-additions.txt` file found under the top-level `blt` directory by adding each file or directory you'd like to exclude on its own line. E.g.,

          /directorytoexclude
          excludeme.txt

1. Change which files are gitignored in the artifact by providing your own `deploy.gitignore_file` value in project.yml. See [upstream .gitignore](https://github.com/acquia/blt/blob/8.x/scripts/blt/deploy/.gitignore) for example contents. E.g.,

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

To modify the behavior of the tests:behat target, you may override BLT's `behat` configuration.

        behat:
          config: ${repo.root}/tests/behat/local.yml
          profile: local
          # The URL of selenium server. Must correspond with setting in behat's yaml config.
          selenium:
            port: 4444
            url: http://127.0.0.1:${behat.selenium.port}/wd/hub
          # An array of paths with behat tests that should be executed.
          paths:
            # - ${docroot}/modules
            # - ${docroot}/profiles
            - ${repo.root}/tests/behat
          tags: '~ajax&&~experimental&&~lightningextension'
          extra: ''
          # May be selenium or phantomjs.
          web-driver: selenium

### validate:*

#### validate:phpcs

To modify the behavior of the validate:phpcs target, you may copy `phpcs.xml.dist` to `phpcs.xml` in your repository root directory and modify the XML. Please see the [official PHPCS documentation](https://github.com/squizlabs/PHP_CodeSniffer/wiki/Advanced-Usage#using-a-default-configuration-file) for more information.