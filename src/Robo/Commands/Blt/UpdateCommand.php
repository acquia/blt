<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\ComposerMunge;
use Acquia\Blt\Robo\Common\YamlMunge;
use Acquia\Blt\Robo\Config\ConfigInitializer;
use Acquia\Blt\Robo\Exceptions\BltException;
use Acquia\Blt\Update\Updater;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Console\Input\InputOption;
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
    $this->updater = $this->getContainer()->get('updater');
    $this->currentSchemaVersion = $this->getInspector()->getCurrentSchemaVersion();
  }

  /**
   * (internal) Generates all necessary files for a brand new BLTed repo.
   *
   * Called during `composer create-project acquia/blt-project`.
   *
   * @command internal:create-project
   *
   * @hidden
   */
  public function createProject() {
    $this->cleanUpProjectTemplate();
    $this->initializeBlt();
    $this->setProjectName();
    $this->initAndCommitRepo();
    $this->displayArt();
    $this->yell("Your new BLT-based project has been created in {$this->getConfigValue('repo.root')}.");
    $this->say("Please continue by following the \"Creating a new project with BLT\" instructions:");
    $this->say("<comment>http://blt.readthedocs.io/en/9.x/readme/creating-new-project/</comment>");
  }

  /**
   * (internal) Prepares a repo that is adding BLT for the first time.
   *
   * @command internal:add-to-project
   *
   * @return \Robo\Result
   *
   * @hidden
   */
  public function addToProject() {
    $this->initializeBlt();
    $this->displayArt();
    $this->yell("BLT has been added to your project.");
    $this->say("This required a full `composer update`.");
    $this->say("BLT has added and modified various project files.");
    $this->say("Please inspect your repository.");
  }

  /**
   * Creates initial BLT files in their default state.
   */
  protected function initializeBlt() {
    $this->updateRootProjectFiles();
    $this->reInstallComposerPackages();

    // Reinitialize configuration now that project files exist.
    $config_initializer = new ConfigInitializer($this->getConfigValue('repo.root'), $this->input());
    $new_config = $config_initializer->initialize();
    $this->getConfig()->import($new_config->export());

    $this->invokeCommand('blt:init:settings');
    $this->invokeCommand('recipes:blt:init:command');
    $this->invokeCommand('blt:init:shell-alias');
    if ($this->input()->isInteractive()) {
      $this->invokeCommand('wizard');
    }
  }

  /**
   * Updates files from BLT's template and executes scripted updates.
   *
   * @command blt:update
   *
   * @aliases bu update
   */
  public function update($options = [
    'since' => InputOption::VALUE_REQUIRED,
  ]) {
    $this->rsyncTemplate();
    $this->mungeProjectYml();

    $starting_version = $options['since'] ?: $this->currentSchemaVersion;
    if ($this->executeSchemaUpdates($starting_version)) {
      $this->updateSchemaVersionFile();
    }
    $this->cleanup();
    $this->invokeCommand('blt:init:shell-alias');
  }

  /**
   * Removes deprecated BLT files and directories.
   *
   * @command blt:source:cleanup
   * @aliases bsc cleanup
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
   *
   * @hidden
   */
  public function initAndCommitRepo() {
    $result = $this->taskExecStack()
      ->dir($this->getConfigValue("repo.root"))
      ->exec("git init")
      ->exec('git add -A')
      ->exec("git commit -m 'Initial commit.'")
      ->interactive(FALSE)
      ->printOutput(FALSE)
      ->printMetadata(FALSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Could not initialize new git repository.");
    }
  }

  /**
   * Displays BLT ASCII art.
   *
   * @command art
   * @hidden
   */
  public function displayArt() {
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

    if (!$result->wasSuccessful()) {
      throw new BltException("Could not remove deprecated files provided by acquia/blt-project.");
    }
  }

  /**
   * Prepares a brand new BLTed repository created by `composer create-project`.
   *
   * @return \Robo\Result
   */
  protected function reInstallComposerPackages() {
    $this->say("Installing new Composer dependencies provided by BLT. This may take a while...");
    $result = $this->taskFilesystemStack()
      ->remove([
        $this->getConfigValue('repo.root') . '/composer.lock',
      ])
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Could not remove Composer files.");
    }

    $result = $this->taskExecStack()
      ->dir($this->getConfigValue('repo.root'))
      ->exec("composer update --no-interaction --ansi")
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Could not install Composer requirements.");
    }
  }

  /**
   * Updates root project files using BLT templated files.
   *
   * @return \Robo\Result
   */
  protected function updateRootProjectFiles() {
    $this->updateSchemaVersionFile();
    $this->rsyncTemplate();
    $this->mungeComposerJson();
    $this->mungeProjectYml();
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
   *
   * @return bool
   *   TRUE if updates were successfully executed.
   */
  protected function executeSchemaUpdates($starting_version) {
    $starting_version = $this->convertLegacySchemaVersion($starting_version);
    $updater = new Updater('Acquia\Blt\Update\Updates', $this->getConfigValue('repo.root'));
    $updates = $updater->getUpdates($starting_version);
    if ($updates) {
      $this->say("<comment>The following BLT updates are outstanding:</comment>");
      $updater->printUpdates($updates);
      // @todo Do not prompt if this is being run from Plugin.php.
      $confirm = $this->confirm('Would you like to perform the listed updates?');
      if ($confirm) {
        try {
          $updater->executeUpdates($updates);
          return TRUE;
        }
        catch (\Exception $e) {
          $this->logger->error($e->getMessage());
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

    if (!$result->wasSuccessful()) {
      throw new BltException("Could not rsync files from BLT into your repository.");
    }
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
    $munged_json = ComposerMunge::mungeFiles($project_composer_json, $template_composer_json);
    $bytes = file_put_contents($project_composer_json, $munged_json);
    if (!$bytes) {
      throw new BltException("Could not update $project_composer_json.");
    }
  }

  /**
   * Updates project BLT .yml files with new key value pairs from upstream.
   *
   * This WILL NOT overwrite existing values.
   */
  protected function mungeProjectYml() {
    $this->say("Merging BLT's <comment>blt.yml</comment> template with your project's <comment>blt/blt.yml</comment>...");
    // Values in the project's existing blt.yml file will be preserved and
    // not overridden.
    $repo_project_yml = $this->getConfigValue('blt.config-files.project');
    $template_project_yml = $this->getConfigValue('blt.root') . '/template/blt/blt.yml';
    $munged_yml = YamlMunge::mungeFiles($template_project_yml, $repo_project_yml);
    try {
      YamlMunge::writeFile($repo_project_yml, $munged_yml);
    }
    catch (\Exception $e) {
      throw new BltException("Could not update $repo_project_yml.");
    }
  }

  /**
   * Sets project.name using the directory name of repot.root.
   *
   * @return \Robo\Result
   */
  protected function setProjectName() {
    $project_name = basename($this->getConfigValue('repo.root'));
    $project_yml = $this->getConfigValue('blt.config-files.project');
    $project_config = YamlMunge::parseFile($project_yml);
    $project_config['project']['machine_name'] = $project_name;
    YamlMunge::writeFile($project_yml, $project_config);
  }

}
