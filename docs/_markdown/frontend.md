# Frontend

Ideally, you will be using a theme that employs SASS/SCSS, a styleguide, and other frontend tools that require some type of build process.

Like Composer dependencies, your frontend dependencies and compiled frontend assets should not be directly committed to the project repository. Instead, they should be built during the creation of a production-ready artifact.

BLT does not directly manage any of your frontend dependencies or assets, but it does create opportunities for you to hook into the build process with your own custom frontend commands.

Additionally, BLT ships with [Cog](https://github.com/acquia-pso/cog), a base theme that provides front end dependencies and front end build tasks compatible with BLT. However you must still manually configure the build steps as described below. See [Cog's documentation](https://github.com/acquia-pso/cog/blob/8.x-1.x/STARTERKIT/README.md#create-cog-sub-theme) for installation notes.

##  Defining frontend build steps

Out of the box, BLT provides three hooks that allow you to define frontend commands that do the following:
 
 1. Install frontend dependencies (i.e. `npm install`)
 2. Build frontend assets (i.e. `npm run build`)
 3. Run frontend tests (i.e. `npm test`)
 
You must define the implementations for each of these hooks in `blt/blt.yml` as described below.

### Install

During the execution of `blt setup` and `blt artifact:deploy`, BLT will execute `command-hooks.frontend-reqs.command`. This hook is intended to provide an opportunity to install the tools required for your frontend build process. For instance, you may use this hook to install dependencies via NPM or Bower, e.g.,

    command-hooks:
      frontend-reqs:
        dir: ${docroot}/themes/custom/[mytheme]
        command: 'npm install'

If you are using a sub theme of Cog, executing `npm install` in your theme directory will install all dependencies listed in [package.json](https://github.com/acquia-pso/cog/blob/8.x-1.x/STARTERKIT/package.json).

Note that `npm install` does _not_ generally install the same versions of packages from one run to the next, which is undesirable when testing and building for production. If you are deploying frontend assets, you may wish to use `npm ci` instead of `npm install` to ensure that dependencies are installed consistently and deterministically. If BLT detects that build files such as `package-lock.json` have changed during the build process, it may fail the deployment to prevent untested code from being deployed. 

### Build

During the execution of `blt setup` and `blt artifact:deploy`, BLT will execute `command-hooks.frontend-assets.command`. This is always executed after `command-hooks.frontend-reqs.command`. This hook is intended to provide an opportunity to compile your frontend assets, such as compiling SCSS to CSS or generating a style guide.

    command-hooks:
      frontend-assets:
        dir: ${docroot}/themes/custom/mytheme
        command: 'npm run build'

If you are using a sub theme of Cog, executing `npm run build` in your theme directory will execute the command defined in `scripts.build` in [package.json](https://github.com/acquia-pso/cog/blob/8.x-1.x/STARTERKIT/package.json#L51).

### Test

During the execution of `blt tests`, BLT will execute `command-hooks.frontend-test.command`. This hook is intended to provide an opportunity execute frontend tests, like JavaScript linting and visual regression testing, e.g.,

    command-hooks:
      frontend-test:
        dir: ${docroot}/themes/custom/mytheme
        command: 'npm test'

If you are using a sub theme of Cog, executing `npm test` in your theme directory will execute the command defined in `scripts.test` in [package.json](https://github.com/acquia-pso/cog/blob/8.x-1.x/STARTERKIT/package.json).

### Executing complex commands

If you need to execute something more complex, you may call a custom script rather than directly embedding your commands in the yaml file:

    command-hooks:
      frontend-assets:
        dir: ${repo.root}
        command: ./scripts/custom/my-script.sh

## System requirements

Strictly speaking, BLT does not have any system requirements for frontend commands. This because BLT does not provide any frontend commands itself. Remember that BLT only provides an opportunity for you to execute your own custom frontend commands. It is your responsibility to determine and install your frontend system requirements, and to ensure that the correct versions of prerequisite tools (NPM, Bower, etc...) are installed wherever you are running deploys (locally, in a VM, or in a CI environment).
