<?php

namespace Acquia\Blt\Robo\Commands\Deploy;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\RandomString;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Finder\Finder;

/**
 * Defines commands in the "deploy:*" namespace.
 */
class DeployCommand extends BltTasks {

  protected $tagName;
  protected $branchName;
  protected $commitMessage;
  protected $excludeFileTemp;
  protected $deployDir;


  /**
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    $this->excludeFileTemp = $this->getConfigValue('deploy.exclude_file') . '.tmp';
    $this->deployDir = $this->getConfigValue('deploy.dir');
  }

  /**
   * Builds separate artifact and pushes to git.remotes defined project.yml
   *
   * @command deploy
   */
  public function deploy() {
    $this->checkDirty();

    $create_tag = FALSE;
    if (empty($this->getConfigValue('deploy.tag'))) {
      $this->say("Typically, you would only create a tag if you currently have a tag checked out on your source repository.");
      $create_tag = $this->confirm("Would you like to create a tag?", $create_tag);
    }
    $this->commitMessage = $this->getCommitMessage();

    if ($create_tag) {
      $this->createTag();
      $this->push($this->tagName);
      $this->tagSourceRepo();
    }
    else {
      $this->createBranch();
      $this->push($this->branchName);
    }
  }

  /**
   * Checks to see if current git branch has uncommitted changes.
   *
   * @throws \Exception
   *   Thrown if deploy.git.failOnDirty is TRUE and there are uncommitted
   *   changes.
   */
  protected function checkDirty() {
    $dirty = (bool) $this->taskExec('git status --porcelain')
      ->printMetadata(FALSE)
      ->printOutput(TRUE)
      ->run();
    if ($dirty) {
      if ($this->getConfigValue('deploy.git.failOnDirty')) {
        throw new \Exception("There are uncommitted changes, commit or stash these changes before deploying.");
      }
      else {
        $this->logger->warning("Deploy is being run with uncommitted changes.");
      }
    }
  }

  /**
   * Gets the commit message to be used for committing deployment artifact.
   *
   * Defaults to the last commit message on the source branch.
   *
   * @return string
   *   The commit message.
   */
  protected function getCommitMessage() {
    if (empty($this->getConfigValue('deploy.commitMsg'))) {
      chdir($this->getConfigValue('repo.root'));
      $git_last_commit_message = explode(' ', shell_exec("git log --oneline -1"), 2);
      return $this->askDefault('Enter a valid commit message', $git_last_commit_message);
    }

    return $this->getConfigValue('deploy.commitMsg');
  }

  /**
   * Gets the branch name to which the deployment artifact will be comitted.
   *
   * Defaults to [current-branch]-build.
   *
   * @return string
   *   The branch name.
   */
  protected function getBranchName() {
    $branch_name = $this->askDefault('Enter the branch name for the deployment artifact', $this->getDefaultBranchName());

    return $branch_name;
  }

  protected function getDefaultBranchName() {
    chdir($this->getConfigValue('repo.root'));
    $git_current_branch = shell_exec("git rev-parse --abbrev-ref HEAD");
    $default_branch = $git_current_branch . '-build';

    return $default_branch;
  }

  /**
   * Creates a deployment artifact and cuts a tag.
   */
  protected function createTag() {
    $this->tagName = $this->ask('Enter the tag name for the deployment artifact');
    // If we are building a tag, then we assume that we will NOT be pushing the
    // build branch from which the tag is created. However, we must still have a
    // local branch from which to cut the tag, so we create a temporary one.
    $this->branchName = $this->getDefaultBranchName() . '-temp';
    $this->prepareDir();
    $this->addGitRemotes();
    $this->checkoutLocalDeployBranch();
    $this->build();
    $this->createDeployId();
  }

  protected function createBranch() {
    $branch_name = $this->getBranchName();
    $this->prepareDir();
    $this->addGitRemotes();

    $this->checkoutLocalDeployBranch();
    $first_git_remote = reset($this->getConfigValue('git.remote'));
    $this->mergeUpstreamChanges($first_git_remote);

    $this->taskExecStack()
      ->dir($this->deployDir)
      ->exec("git tag -a {$this->tagName} -m '{$this->commitMessage}'")
      ->run();
  }

  /**
   * Deletes the existing deploy directory and initializes git repo.
   */
  protected function prepareDir() {
    $deploy_dir = $this->deployDir;
    $this->taskDeleteDir()->run($deploy_dir);
    $this->taskExecStack()
      ->dir($deploy_dir)
      ->exec("git init")
      ->exec("git config --local core.excludesfile false")
      ->run();
    $this->say("Global .gitignore file is being disabled for this repository to prevent unexpected behavior.");
    if ($this->getConfig()->has("git.user.name") &&
      $this->getConfig()->has("git.user.email")) {
      $git_user = $this->getConfigValue("git.user.name");
      $git_email = $this->getConfigValue("git.user.email");
      $this->taskExecStack()
        ->dir($this->deployDir)
        ->exec("git config --local --add user.name '$git_user'")
        ->exec("git config --local --add user.email '$git_email'")
        ->run();
    }
  }

  protected function addGitRemotes() {
    // Add remotes and fetch upstream refs.
    $git_remotes = $this->getConfigValue('git.remotes');
    foreach ($git_remotes as $remote_url) {
      $this->addGitRemote($remote_url);
    }
  }

  protected function addGitRemote($remote_url) {
    $this->say("Fetching from git remote $remote_url");
    // Generate an md5 sum of the remote URL to use as remote name.
    $remote_name = md5($remote_url);
    $this->taskExec("git remote add $remote_name $remote_url")
      ->dir($this->deployDir)
      ->run();
  }

  protected function checkoutLocalDeployBranch() {
    $this->taskExecStack()
      ->dir($this->deployDir)
      // Create new branch locally.We intentionally use stopOnFail(FALSE) in
      // case the branch already exists. `git checkout -B` does not seem to work
      // as advertised.
      // @todo perform this in a way that avoid errors completely.
      ->stopOnFail(FALSE)
      ->exec("git checkout -b {$this->branchName}")
      ->run();
  }

  protected function mergeUpstreamChanges($remote_name) {
    $this->taskExecStack()
      ->dir($this->deployDir)
      ->stopOnFail(FALSE)
      ->exec("git fetch $remote_name {$this->branchName}")
      ->exec("git merge $remote_name/{$this->branchName}")
      ->run();
  }

  protected function build() {
    $exit_code = $this->invokeCommands([
      // Execute `blt frontend` to ensure that frontend artifact are generated
      // in source repo.
      'frontend',
      // Execute `setup:hash-salt` to ensure that salt.txt exists. There's a
      // slim chance this has never been generated.
      'setup:hash-salt',
    ]);
    if ($exit_code) {
      return $exit_code;
    }

    $this->buildCopy();
    $this->createDeployId();
    $this->invokeHook("post-deploy-build");


  }

  protected function buildCopy() {

    if ($this->getConfigValue('deploy.build-dependencies')) {
      $this->logger->warning("Dependencies will not be built because deploy.build-dependencies is not enabled");
      $this->logger->warning("You should define a custom deploy.exclude_file to ensure that dependencies are copied from the root repository.");

      return FALSE;
    }

    $exclude_list_file = $this->getExcludeListFile();
    $source = $this->getConfigValue('repo.root');
    $dest = $this->deployDir;

    $this->setMultisiteFilePermissions(0777);
    $this->taskExec("rsync -a --no-g --delete --delete-excluded --exclude-from='$exclude_list_file' '$source/' '$dest/' --filter 'protect /.git/'")
      ->dir($this->getConfigValue('repo.root'))
      ->run();
    $this->setMultisiteFilePermissions(0755);

    // Remove temporary file that may have been created by $this->getExcludeListFile().
    $this->_remove($this->excludeFileTemp);

    $this->taskFilesystemStack()
      ->copy(
        $this->getConfigValue('deploy.gitignore_file'),
        $this->deployDir . '/.gitignore', TRUE
      )
      ->run();

  }

  protected function composerInstall() {
    $this->say("Rebuilding composer dependencies for production...");
    $this->taskDeleteDir([$this->deployDir . '/vendor'])->run();
    $this->taskFilesystemStack()
      ->copy($this->getConfigValue('repo.root') . '/composer.json', $this->deployDir . '/composer.json')
      ->copy($this->getConfigValue('repo.root') . '/composer.lock', $this->deployDir . '/composer.lock')
      ->run();
    $this->taskExec("composer install --no-dev --no-interaction --optimize-autoloader")
      ->dir($this->deployDir)
      ->run();
  }

  protected function createDeployId() {
    $this->taskExec("echo '{$this->tagName}' > deployment_identifier")
      ->dir($this->deployDir)
      ->run();
  }

  /**
   * Removes sensitive files from the deploy dir.
   */
  protected function sanitize() {
    $this->taskExecStack()
      ->exec("find '{$this->deployDir}/vendor' -type d | grep '\.git' | xargs rm -rf")
      ->exec("find '{$this->deployDir}/docroot' -type d | grep '\.git' | xargs rm -rf")
      ->run();

    $taskFilesystemStack = $this->taskFilesystemStack();

    $finder = new Finder();
    $files = $finder
      ->in($this->deployDir)
      ->files()
      ->name('CHANGELOG.txt');

    foreach ($files->getIterator() as $item) {
      $taskFilesystemStack->remove($item->getRealPath());
    }

    $finder = new Finder();
    $files = $finder
      ->in($this->deployDir . '/core')
      ->files()
      ->name('*.txt');

    foreach ($files->getIterator() as $item) {
      $taskFilesystemStack->remove($item->getRealPath());
    }

    $taskFilesystemStack->run();
  }

  protected function getExcludeListFile() {
    $exclude_file = $this->getConfigValue('deploy.exclude_file');
    $exclude_additions = $this->getConfigValue('deploy.exclude_additions_file');
    if (file_exists($exclude_additions)) {
      $this->say("Combining exclusions from deploy.deploy-exclude-additions and deploy.deploy-exclude files...");
      $exclude_file = $this->mungeExcludeLists($exclude_file, $exclude_additions);
    }

    return $exclude_file;
  }

  protected function mungeExcludeLists($file1, $file2) {
    $file1_contents = file($file1);
    $file2_contents = file($file2);
    $merged = array_merge($file1_contents, $file2_contents);
    $merged_without_dups = array_unique($merged);
    file_put_contents($this->excludeFileTemp, $merged_without_dups);

    return $this->excludeFileTemp;
  }

  /**
   * Sets permissions for all multisite directories.
   */
  protected function setMultisiteFilePermissions($perms) {
    $taskFilesystemStack = $this->taskFilesystemStack();
    $multisites = $this->getConfigValue('multisites');
    foreach ($multisites as $multisite) {
      $multisite_dir = $this->getConfigValue('docroot') . '/sites/' . $multisite;
      $taskFilesystemStack->chmod($multisite_dir, $perms);
    }
    $taskFilesystemStack->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE);
    $taskFilesystemStack->run();
  }

  protected function commit() {
    $this->taskExecStack()
      ->dir($this->deployDir)
      ->exec("git add -A")
      ->exec("git commit --quiet -m '{$this->commitMessage}'")
      ->run();
  }


  protected function push($identifier) {
    if ($this->getConfigValue('deploy.dryRun')) {
      $this->logger->warning("Skipping push of deployment artifact. deploy.dryRun is set to true.");
      return TRUE;
    }

    $task = $this->taskExecStack()
      ->dir($this->deployDir);
    foreach ($this->getConfigValue('git.remotes') as $remote) {
      $remote_name = md5($remote);
        $task->exec("git push $remote_name $identifier");
    }
    $task->run();
  }

  protected function tagSourceRepo() {
    $this->taskExec("git tag -a {$this->tagName} -m '{$this->commitMessage}'")
      ->dir($this->deployDir)
      ->run();
  }

  protected function deploySamlConfig() {
    if ($this->getConfigValue('simplesamlphp')) {
      $this->taskExec("blt simplesamlphp:deploy:config")
        ->dir($this->getConfigValue('repo.root'))
        ->run();
    }
  }

  protected function updateSites() {
    foreach ($this->getConfigValue('multisites') as $multisite) {
      $this->say("Deploying updates to $multisite...");

      $status_code = $this->invokeCommand('setup:config-import', [
          // Most sites store their version-controlled configuration in /config/default.
          // ACE internally sets the vcs configuration directory to /config/default, so we use that.
          '--define cm.core.key=' . $this->getConfigValue('cm.core.deploy-key'),
          // Disable alias since we are targeting specific uri.
          '--define drush.alias=""',
          "--define drush.uri='$multisite'",
        ]
      );
      if (!$status_code) {
        return $status_code;
      }
      $status_code = $this->invokeCommand('setup:toggle-modules');
      if (!$status_code) {
        return $status_code;
      }

      $this->say("Finished deploying updates to $multisite.");
    }
  }

  protected function installDrupal() {
    $status_code = $this->invokeCommands([
      'drupal:install',
      'drupal:update',
    ]);
    if ($status_code) {
      return $status_code;
    }

    $this->updateSites();
  }
}
