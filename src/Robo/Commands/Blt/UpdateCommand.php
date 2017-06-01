<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\ComposerMunge;
use Acquia\Blt\Robo\Common\YamlMunge;
use Acquia\Blt\Update\Updater;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Defines commands for installing and updating BLT..
 */
class UpdateCommand extends BltTasks {

  /**
   * @var \Acquia\Blt\Update\Updater
   */
  protected $updater;

  /**
   * @var string
   */
  protected $currentSchemaVersion;

  /**
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    $this->updater = new Updater('Acquia\Blt\Update\Updates', $this->getConfigValue('repo.root'));
    $this->currentSchemaVersion = $this->getCurrentSchemaVersion();
  }

  /**
   * (internal) Generates all necessary files for a brand new BLTed repo.
   *
   * Called during `composer create-project acquia/blt-project`.
   *
   * @command internal:create-project
   */
  public function createProject() {
    $result = $this->cleanUpProjectTemplate();
    $result = $this->updateRootProjectFiles();
    $result = $this->reInstallComposerPackages();
    $result = $this->setProjectName();
    $result = $this->initAndCommitRepo();
    $exit_code = $this->invokeCommand('install-alias');
    $this->displayArt();

    $this->yell("Your new BLT-based project has been created in {$this->getConfigValue('repo.root')}.");
    $this->say("Please continue by following the \"Creating a new project with BLT\" instructions:");
    $this->say("<comment>http://blt.readthedocs.io/en/8.x/readme/creating-new-project/</comment>");

    return $result->getExitCode();
  }

  /**
   * Updates files from BLT's template and executes scripted updates.
   *
   * @command update
   */
  public function update() {
    $this->rsyncTemplate();
    $this->mungeProjectYml();
    if ($this->executeSchemaUpdates($this->currentSchemaVersion)) {
      $this->updateSchemaVersionFile();
    }
    $this->cleanup();
    $exit_code = $this->invokeCommand('install-alias');

    return $exit_code;
  }

  /**
   * (internal) Prepares a repo that is adding BLT for the first time.
   *
   * @command internal:add-to-project
   *
   * @return \Robo\Result
   */
  public function addToProject() {
    $result = $this->reInstallComposerPackages();
    $this->displayArt();
    $this->yell("BLT has been added to your project.");
    $this->say("It has added and modified various project files. Please inspect your repository.");

    return $result;
  }

  /**
   * Removes deprecated BLT files and directories.
   *
   * @command cleanup
   */
  public function cleanup() {
    $this->say("Removing deprecated files and directories...");
    $this->taskFilesystemStack()
      ->remove([
        "build",
        "docroot/sites/default/settings/apcu_fix.yml",
        "docroot/sites/default/settings/base.settings.php",
        "docroot/sites/default/settings/blt.settings.php",
        "docroot/sites/default/settings/cache.settings.php",
        "docroot/sites/default/settings/filesystem.settings.php",
        "docroot/sites/default/settings/logging.settings.php",
        "docroot/sites/default/settings/travis.settings.php",
        "docroot/sites/default/settings/pipelines.settings.php",
        "docroot/sites/default/settings/tugboat.settings.php",
        "tests/phpunit/blt",
        "tests/phpunit/Bolt",
        "scripts/blt",
        "scripts/drupal",
        "scripts/drupal-vm",
        "scripts/git-hooks",
        "scripts/release-notes",
        "scripts/tugboat",
        "blt.sh",
        "project.yml",
        "project.local.yml",
        "example.project.local.yml",
        "readme/acsf-setup.md",
        "readme/architecture.md",
        "readme/best-practices.md",
        "readme/deploy.md",
        "readme/dev-workflow.md",
        "readme/features-workflow.md",
        "readme/local-development.md",
        "readme/onboarding.md",
        "readme/project-tasks.md",
        "readme/release-process.md",
        "readme/repo-architecture.md",
        "readme/views.md",
        "drush/policy.drush.inc",
      ])
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
  }

  /**
   * (internal) Initializes the project repo and performs initial commit.
   *
   * @command internal:create-project:init-repo
   */
  public function initAndCommitRepo() {
    $result = $this->taskExecStack()
      ->dir("repo.root")
      ->exec("git init")
      ->exec('git add -A')
      ->exec("git commit -m 'Initial commit.'")
      ->interactive(FALSE)
      ->printOutput(FALSE)
      ->run();

    return $result;

  }

  /**
   * Displays BLT ASCII art.
   */
  protected function displayArt() {
    $this->say(file_get_contents($this->getConfigValue('blt.root') . '/scripts/blt/ascii-art.txt'));
  }

  /**
   * Cleans up undesired files left behind by acquia/blt-project.
   *
   * @return \Robo\Result
   */
  protected function cleanUpProjectTemplate() {
    // Remove files leftover from acquia/blt-project.
    $result = $this->taskFilesystemStack()
      ->remove($this->getConfigValue('repo.root') . '/.travis.yml')
      ->remove($this->getConfigValue('repo.root') . '/LICENSE.txt')
      ->remove($this->getConfigValue('repo.root') . '/README.md')
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    return $result;
  }

  /**
   * Prepares a brand new BLTed repository created by `composer create-project`.
   *
   * @return \Robo\Result
   */
  protected function reInstallComposerPackages() {
    $this->say("Installing new Composer dependencies provided by BLT. This make take a while...");
    $result = $this->taskFilesystemStack()
      ->remove([
        $this->getConfigValue('repo.root') . '/composer.lock',
        $this->getConfigValue('repo.root') . '/vendor',
      ])
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
    $result = $this->taskExecStack()
      ->dir($this->getConfigValue('repo.root'))
      ->exec("composer install --no-interaction --prefer-dist")
      ->detectInteractive()
      ->run();

    return $result;
  }

  /**
   * Updates root project files using BLT templated files.
   *
   * @return \Robo\Result
   */
  protected function updateRootProjectFiles() {
    $this->updateSchemaVersionFile();
    $result = $this->rsyncTemplate();
    $result = $this->mungeComposerJson();
    $result = $this->mungeProjectYml();

    return $result;
  }

  /**
   * Gets the current schema version of the root project.
   *
   * @return string
   *   The current schema version.
   */
  protected function getCurrentSchemaVersion() {
    if (file_exists($this->getConfigValue('blt.config-files.schema-version'))) {
      $version = file_get_contents($this->getConfigValue('blt.config-files.schema-version'));
    }
    else {
      $version = $this->updater->getLatestUpdateMethodVersion();
    }

    return $version;
  }

  /**
   * Updates blt/.schema_version with latest schema version.
   */
  protected function updateSchemaVersionFile() {
    // Write BLT version to blt/.schema-version.
    $latest_update_method_version = $this->updater->getLatestUpdateMethodVersion();
    $schema_file_name = $this->getConfigValue('blt.config-files.schema-version');

    $fs = new Filesystem();
    $fs->dumpFile($schema_file_name, $latest_update_method_version);
  }

  /**
   * Executes all update hooks for a given schema delta.
   *
   * @param $starting_version
   */
  protected function executeSchemaUpdates($starting_version) {
    $starting_version = $this->convertLegacySchemaVersion($starting_version);
    $updater = new Updater('Acquia\Blt\Update\Updates', $this->getConfigValue('repo.root'));
    $updates = $updater->getUpdates($starting_version);
    if ($updates) {
      $this->say("<comment>The following BLT updates are outstanding:</comment>");
      $updater->printUpdates($updates);
      $confirm = $this->confirm('Would you like to perform the listed updates?');
      if ($confirm) {
        try {
          $updater->executeUpdates($updates);
          return TRUE;
        }
        catch (\Exception $e) {
          return FALSE;
        }
      }
    }
  }

  /**
   * Converts legacy BLT schema version to current version.
   *
   * @param string $version
   *   The legacy version.
   *
   * @return string
   *   The version in correct syntax.
   */
  protected function convertLegacySchemaVersion($version) {
    // Check to see if version is Semver (legacy format). Convert to expected
    // syntax. Luckily, there are a finite number of known legacy versions.
    // We check specifically for those.
    // E.g., 8.6.6 => 8006006.
    if (strpos($version, '.') !== FALSE) {
      str_replace('-beta1', '', $version);
      $semver_array = explode('.', $version);
      $semver_array[1] = str_pad($semver_array[1], 3, "0", STR_PAD_LEFT);
      $semver_array[2] = str_pad($semver_array[2], 3, "0", STR_PAD_LEFT);
      $version = implode('', $semver_array);
    }
    if (strpos($version, 'dev') !== FALSE) {
      $version = '0';
    }
    return $version;
  }

  /**
   * Rsyncs files from BLT's template dir into project root dir.
   *
   * @return \Robo\Result
   */
  protected function rsyncTemplate() {
    $source = $this->getConfigValue('blt.root') . '/template';
    $destination = $this->getConfigValue('repo.root');
    $exclude_from = $this->getConfigValue('blt.update.ignore-existing-file');
    $this->say("Copying files from BLT's template into your project...");
    $result = $this->taskExecStack()
      ->exec("rsync -a --no-g '$source/' '$destination/' --exclude-from='$exclude_from'")
      ->exec("rsync -a --no-g '$source/' '$destination/' --include-from='$exclude_from' --ignore-existing")
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    return $result;
  }

  /**
   * Munges BLT's templated composer.json with project's composer.json.
   */
  protected function mungeComposerJson() {
    // Merge in the extras configuration. This pulls in
    // wikimedia/composer-merge-plugin and composer/installers settings.
    $this->say("Merging default configuration into composer.json...");
    $project_composer_json = $this->getConfigValue('repo.root') . '/composer.json';
    $template_composer_json = $this->getConfigValue('blt.root') . '/template/composer.json';
    $munged_json = ComposerMunge::munge($project_composer_json, $template_composer_json);
    file_put_contents($project_composer_json, $munged_json);
  }

  /**
   * Updates project BLT .yml files with new key value pairs from upstream.
   *
   * This WILL NOT overwrite existing values.
   */
  protected function mungeProjectYml() {
    $this->say("Merging BLT's <comment>project.yml</comment> template with your project's <comment>blt/project.yml</comment>...");
    // Values in the project's existing project.yml file will be preserved and
    // not overridden.
    $munged_yaml = YamlMunge::munge($this->getConfigValue('blt.root') . '/template/blt/project.yml', $this->getConfigValue('blt.config-files.project'));
    file_put_contents($this->getConfigValue('blt.config-files.project'), $munged_yaml);
  }

  /**
   * Sets project.name using the directory name of repot.root.
   *
   * @return \Robo\Result
   */
  protected function setProjectName() {
    $project_name = basename($this->getConfigValue('repo.root'));
    $result = $this->taskExecStack()
      ->exec("{$this->getConfigValue('composer.bin')}/yaml-cli update:value {$this->getConfigValue('blt.config-files.project')} project.machine_name '$project_name'")
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
    return $result;
  }

}
