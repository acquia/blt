## Setup
[Probo.CI](https://probo.ci/) is a continuous integration tool that runs tests and builds sandbox environments for each of your pull requests. It can be set up to work with your Acquia BLT project in a few steps.

### Connect to the Probo app
1. Connect your GitHub or Bitbucket account to Probo at [https://app.probo.ci/](https://app.probo.ci/).
2. Once logged in, activate your repository at [https://app.probo.ci/#/dashboard/repos](https://app.probo.ci/#/dashboard/repos).

### Generate a configuration file
1. Create a new branch in your repository.
2. Run the following command from the project root:
  ```
  blt recipes:ci:probo:init
  ```
3. Commit and push the generated `.probo.yaml` file.
4. Create a pull request for your branch.

Probo will start to build a sandbox site based on the default configuration provided in the `.probo.yaml` file. You can access this environment from the pull request or at [https://app.probo.ci/](https://app.probo.ci/). See [https://docs.probo.ci/](https://docs.probo.ci/) for information about adding custom build steps and other configuration details.
