# Frontend

Ideally, you will be using a theme that employs SASS/SCSS, a styleguide, and other frontend tools that require some type of build process.

Like Composer dependencies, your frontend dependencies and compiled frontend assets should not be directly committed to the project repository. Instead, they should be built during the creation of a production-ready artifact.

BLT does not directly manage any of your front end dependencies or assets, but it does create opportunities for you to hook into the build process with your own custom frontend commands. Additionally, BLT ships with [Cog](https://github.com/acquia-pso/cog), a base theme that provides front end dependencies and front end build tasks compatible with BLT.

##  Available target hooks

Out-of-the-box, BLT provides an opportunity for your frontend commands to run at three different stages of the build process.

You must let BLT know which commands should be run in which directories. You can do this by specifying values in `blt/blt.yml` file under the `command-hooks` key.

These commands will run inside of the virtual machine, if available. This obviates the need to install frontend dependencies on the host machine.

The three following target hooks are available for frontend commands: setup, build, test.

### Setup

During the execution of `blt setup`, BLT will execute `command-hooks.frontend-reqs.command`. This hook is intended to provide an opportunity to install the tools required for your frontend build process. For instance, you may use this hook to install dependencies via NPM or Bower, e.g.,

    command-hooks:
      frontend-reqs:
        dir: ${docroot}/themes/custom/mytheme
        command: '[ -s "$NVM_DIR/nvm.sh" ] && . "$NVM_DIR/nvm.sh" && nvm use 4.4.4 && npm install'

If you are using a sub theme of Cog, executing `npm install` in your theme directory (as exemplified above) will install all dependencies listed in [package.json](https://github.com/acquia-pso/cog/blob/8.x-1.x/STARTERKIT/package.json).

### Build

During the execution of `blt setup` and `blt artifact:deploy`, BLT will execute `command-hooks.frontend-assets.command`. This is always executed after `command-hooks.frontend-reqs.command`. This hook is intended to provide an opportunity to compile your frontend assets, such as compiling SCSS to CSS or generating a style guide.

    command-hooks:
      frontend-assets:
        dir: ${docroot}/themes/custom/mytheme
        command: '[ -s "$NVM_DIR/nvm.sh" ] && . "$NVM_DIR/nvm.sh" && nvm use 4.4.4 && npm run build'

If you are using a sub theme of Cog, executing `npm run build` in your theme directory (as exemplified above) will execute the command defined in `scripts.build` in [package.json](https://github.com/acquia-pso/cog/blob/8.x-1.x/STARTERKIT/package.json#L51).

### Test

During the execution of `blt tests`, BLT will execute `command-hooks.frontend-test.command`. This hook is intended to provide an opportunity execute frontend tests, like JavaScript linting and visual regression testing, e.g.,

    command-hooks:
      frontend-test:
        dir: ${docroot}/themes/custom/mytheme
        command: '[ -s "$NVM_DIR/nvm.sh" ] && . "$NVM_DIR/nvm.sh" && nvm use 4.4.4 && npm test'

If you are using a sub theme of Cog, executing `npm test` in your theme directory (as exemplified above) will execute the command defined in `scripts.test` in [package.json](https://github.com/acquia-pso/cog/blob/8.x-1.x/STARTERKIT/package.json).

### Executing complex commands

If you need to execute something more complex, you may call a custom script rather than directly embedding your commands in the yaml file:

    command-hooks:
      frontend-assets:
        dir: ${repo.root}
        command: ./scripts/custom/my-script.sh

## System requirements

Strictly speaking, BLT does not have any system requirements for frontend commands. This because BLT does not provide any frontend commands itself. Remember that BLT only provides an opportunity for you to execute your own custom frontend commands. It is your responsibility to determine and install your frontend system requirements.

However, it is recommended that you manage frontend dependencies using `npm` and that you manage multiple Node JS versions (if applicable) via `nvm`.

You can install these two tools on OSX using [Homebrew](https://brew.sh/):

    brew install npm nvm

_If you are using Drupal VM, then these system requirements need only be available inside the virtual machine_, not on the host machine. Frontend commands will run inside of the virtual machine, if available.
