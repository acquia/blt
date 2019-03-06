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

You may disable any BLT command. This will cause the target to be skipped during the normal build process. To disable a target, add a `disable-targets` key to your blt.yml file:

      disable-targets:
        tests:
          phpcs:
            sniff:
              all: true
              files: true
              
This snippet would cause the `tests:phpcs:sniff:all` and `tests:phpcs:sniff:files` targets to be skipped during BLT builds.

## Adding / overriding filesets

To modify the behavior of PHPCS, see [tests:phpcs:sniff:all](#testsphpcssniffall) documentation.

To modify the filesets that are used in other commands, such as `tests:twig:lint:all`, `tests:yaml:lint:all`, and `tests:php:lint`:

1. Generate an example `Filesets.php` file by executing `blt example:init`. You may use the generated file as a guide for writing your own filesite.
1. Create a public method in the `Filesets` class in the generated file.
1. Add a Fileset annotation to your public method, specifying its id:

        @fileset(id="files.yaml.custom")

1. Instantiate and return a `Symfony\Component\Finder\Finder` object. The files found by the finder comprise the fileset.
1. You may use the Fileset id in various configuration values in your `blt/blt.yml` file, e.g., modify `tests:yaml:lint:all` such that it scans only your custom fileset, you would add the following to `blt/blt.yml`:

        validate:
          yaml:
            filesets:
              - files.yaml.custom

## Modifying BLT Configuration

BLT configuration can be customized by overriding the value of default variable values. You can find the default value of any BLT variable in [build.yml](https://github.com/acquia/blt/blob/9.x/config/build.yml).

### Overriding a variable value:

Configuration values are loaded, in this order, from the following list of YAML files:

- vendor/acquia/blt/config/build.yml
- blt/blt.yml
- blt/[environment].blt.yml
- docroot/sites/[site]/blt.yml
- docroot/sites/[site]/[environment].blt.yml

Values loaded from the later files will overwrite values in earlier files. Note, if you would like to override a non-empty value with an empty value, the override value must be set to `null` and not `''` or `[]`.

### Overriding project-wide

You can override any variable value by adding an entry for that variable to your `blt/blt.yml` file. This change will be committed to your repository and shared by all developers for the project. For example:

        behat.tags: @mytags

### Overriding locally

You can override a variable value for your local machine in the same way that you can for specific environments. See next section, use "local" for environment value.

### Overriding in specific environments

You may override a variable value for specific environments, such as a the `local` or `ci` environments, by adding an entry for that variable to a file named in the pattern [environment].blt.yml. For instance, ci.blt.yml.

At present, only the `local` and `ci` environment is automatically detected. You may pass `--environment` as an argument to BLT to specify the correct environmental configuration to load.

### Overriding at runtime

You may overwrite a variable value at runtime by specifying the variable value in your `blt` command using argument syntax `-D [key]=[value]`, e.g.,

        blt tests:behat:run -D behat.tags='@mytags'

For configuration values that are indexed arrays, you can override individual values using the numeric index, such as `git.remotes.0`.

Listed below are some of the more commonly customized BLT targets.

### artifact:*

#### artifact:build

To modify the behavior of the `artifact:build` target, you may override BLT's `deploy` configuration. See `deploy` key in https://github.com/acquia/blt/blob/9.x/config/build.yml#L54.

More specifically, you can modify the build artifact in the following key ways:

1. Change which files are rsynced to the artifact by providing your own `deploy.exclude_file` value in blt.yml. See [upstream deploy-exclude.txt](https://github.com/acquia/blt/blob/9.x/scripts/blt/deploy/deploy-exclude.txt) for example contents, e.g.,

          deploy:
            exclude_file: ${repo.root}/blt/deploy/rsync-exclude.txt

1. If you'd simply like to add onto the [upstream deploy-exclude.txt](https://github.com/acquia/blt/blob/9.x/scripts/blt/deploy/deploy-exclude.txt) instead of overriding it, you need not define your own `deploy.exclude_file`. Instead, simply leverage the `deploy-exclude-additions.txt` file found under the top-level `blt` directory by adding each file or directory you'd like to exclude on its own line, e.g.,

          /directorytoexclude
          excludeme.txt

1. Change which files are gitignored in the artifact by providing your own `deploy.gitignore_file` value in blt.yml. See [upstream .gitignore](https://github.com/acquia/blt/blob/9.x/scripts/blt/deploy/.gitignore) for example contents, e.g.,

          deploy:
            gitignore_file: ${repo.root}/blt/deploy/.gitignore

1. Execute a custom command after the artifact by providing your own `command-hooks.post-deploy-build.dir` and `command-hooks.post-deploy-build.command` values in blt.yml, e.g.,

          # Executed after deployment artifact is created.
          post-deploy-build:
            dir: ${deploy.dir}/docroot/profiles/contrib/lightning
            command: npm run install-libraries
            
   Or, use a Robo hook in a custom file (see "Adding a custom Robo Hook" above).
   
       /**
         * This will be called after the artifact:build command.
         *
         * @hook post-command artifact:build
         */
        public function postArtifactBuild() {
          $this->doSomething();
        }

### git hooks

You may disable a git hook by setting its value under `git.hooks` to false:

        git:
          hooks:
            pre-commit: false

You may use a custom git hook in place of BLT's default git hooks by setting its value under `git.hooks` to the directory path containing of the hook. The directory must contain an executable file named after the git hook:

        git:
          hooks:
            pre-commit: ${repo.root}/my-custom-git-hooks

In this example, an executable file named `pre-commit` should exist in `${repo.root}/my-custom-git-hooks`.

You should execute `blt blt:init:git-hooks` after modifying these values in order for changes to take effect. Also note that most projects will already have a `git` key in their `blt.yml` file, make sure to append `hooks` to this existing key.

#### commit-msg

By default, BLT will execute the `internal:git-hook:execute:commit-msg` command when new git commits are made. This command validates that the commit message matches the regular expression defined in `git.commit-msg.pattern`. You may [override the default configuration](#modifying-blt-configuration).

### tests:*

#### tests:behat:run

To modify the behavior of the tests:behat:run target, you may override BLT's `behat` configuration. See https://github.com/acquia/blt/blob/9.x/config/build.yml#L2.

#### tests:phpcs:sniff:all

To modify the behavior of the tests:phpcs:sniff:all target, you may copy `phpcs.xml.dist` to `phpcs.xml` in your repository root directory and modify the XML. Please see the [official PHPCS documentation](https://github.com/squizlabs/PHP_CodeSniffer/wiki/Advanced-Usage#using-a-default-configuration-file) for more information.

#### tests:twig:lint:all

To prevent validation failures on any Twig filters or functions created in custom or contrib module `twig.extension` services, add `filters` and `functions` like so:

        validate:
          twig:
            filters:
              - my_filter_1
              - my_filter_2
            functions:
              - my_function_1
              - my_function_2
