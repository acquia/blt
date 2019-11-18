<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\YamlWriter;
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
   * Updater.
   *
   * @var \Acquia\Blt\Update\Updater
   */
  protected $updater;

  /**
   * Current schema.
   *
   * @var string
   */
  protected $currentSchemaVersion;

  /**
   * Exclude files.
   *
   * @var array
   * Files that exist in the BLT Project repo but aren't actually part of the
   * project template. They are only used for testing, licensing, etc...
   */
  const BLT_PROJECT_EXCLUDE_FILES = [
    '.test-packages.json',
    '.travis.yml',
    'LICENSE.txt',
    'README.md',
    'README-template.md',
  ];

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
   * Generates all necessary files for a brand new BLTed repo.
   *
   * Called during `composer create-project acquia/blt-project`.
   *
   * @command internal:create-project
   *
   * @hidden
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function createProject() {
    $this->cleanUpProjectTemplate();
    $this->initializeBlt();
    $this->setProjectName();
    $this->initAndCommitRepo();
    $this->displayArt();
    $this->yell("Your new BLT-based project has been created in {$this->getConfigValue('repo.root')}.");
    $this->say("Please continue by following the \"Creating a new project with BLT\" instructions:");
    $this->say("<comment>https://docs.acquia.com/blt/install/creating-new-project/</comment>");
  }

  /**
   * Prepares a repo that is adding BLT for the first time.
   *
   * @command internal:add-to-project
   *
   * @hidden
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function addToProject() {
    $this->rsyncTemplate();
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
    $this->updateSchemaVersionFile();
    $this->taskExecStack()
      ->dir($this->getConfigValue("repo.root"))
      ->exec("composer drupal:scaffold")
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    // Reinitialize configuration now that project files exist.
    $config_initializer = new ConfigInitializer($this->getConfigValue('repo.root'), $this->input());
    $new_config = $config_initializer->initialize();
    $this->getConfig()->replace($new_config->export());

    $this->invokeCommand('blt:init:settings');
    $this->invokeCommand('blt:init:shell-alias');
    if (DIRECTORY_SEPARATOR === '\\') {
      // On Windows, during composer create-project,
      // the wizard command fails when it reaches the interactive steps.
      // Until this is fixed, go with the defaults.
      // The user can run blt wizard any time later for changing defaults.
      $this->input()->setInteractive(FALSE);
    }
    if ($this->input()->isInteractive()) {
      $this->invokeCommand('wizard');
    }
  }

  /**
   * Updates files from BLT's template and executes scripted updates.
   *
   * @param array $options
   *   Options.
   *
   * @command blt:update
   *
   * @aliases bu update
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function update(array $options = ['since' => InputOption::VALUE_REQUIRED]) {
    $this->rsyncTemplate();

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
   * @command blt:update:cleanup
   * @aliases blt:source:cleanup bsc cleanup
   */
  public function cleanup() {
    $this->say("Removing deprecated files and directories...");
    $this->taskFilesystemStack()
      ->remove([
        "blt/composer.required.json",
        "blt/composer.suggested.json",
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
        "docroot/sites/settings/global.settings.default.php",
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
        ".test-packages.json",
      ])
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
  }

  /**
   * Initializes the project repo and performs initial commit.
   *
   * @command internal:create-project:init-repo
   *
   * @hidden
   *
   * @validateGitConfig
   */
  public function initAndCommitRepo() {
    // The .git dir will already exist if blt-project was created using a
    // branch. Otherwise, it will not exist when using a tag.
    if (!file_exists($this->getConfigValue("repo.root") . "/.git")) {
      $result = $this->taskGit()
        ->dir($this->getConfigValue("repo.root"))
        ->exec("git init")
        ->commit('Initial commit.', '--allow-empty')
        ->add('-A')
        ->commit('Created BLT project.')
        ->interactive(FALSE)
        ->printOutput(FALSE)
        ->printMetadata(FALSE)
        ->run();

      if (!$result->wasSuccessful()) {
        throw new BltException("Could not initialize new git repository.");
      }
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
   */
  protected function cleanUpProjectTemplate() {
    $repo_root = $this->getConfigValue('repo.root');
    // Remove files leftover from acquia/blt-project.
    $cleanupTask = $this->taskFilesystemStack();
    foreach (self::BLT_PROJECT_EXCLUDE_FILES as $file) {
      if ($file != 'README-template.md') {
        $cleanupTask->remove($repo_root . '/' . $file);
      }
    }
    $result = $cleanupTask
      ->rename($repo_root . '/README-template.md', $repo_root . '/README.md', TRUE)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Could not remove deprecated files provided by acquia/blt-project.");
    }
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
   * @param string $starting_version
   *   Starting version.
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
      $confirm = $this->confirm('Would you like to perform the listed updates?', TRUE);
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
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function rsyncTemplate() {
    $source = $this->getConfigValue('blt.root') . '/subtree-splits/blt-project';
    $destination = $this->getConfigValue('repo.root');
    // There is no native rsync on Windows.
    // The most used one on Windows is https://itefix.net/cwrsync,
    // which runs with cygwin, so doesn't cope with regular Windows paths.
    if (DIRECTORY_SEPARATOR === '\\') {
      $source = $this->convertWindowsPathToCygwinPath($source);
      $destination = $this->convertWindowsPathToCygwinPath($destination);
    }
    $exclude_from = $this->getConfigValue('blt.update.ignore-existing-file');
    $this->say("Copying files from BLT's template into your project...");
    $rsync_command1 = "rsync -a --no-g '$source/' '$destination/' --exclude-from='$exclude_from'";
    $rsync_command2 = "rsync -a --no-g '$source/' '$destination/' --include-from='$exclude_from' --ignore-existing";
    foreach (self::BLT_PROJECT_EXCLUDE_FILES as $file) {
      $rsync_command1 .= " --exclude=$file";
      $rsync_command2 .= " --exclude=$file";
    }
    $result = $this->taskExecStack()
      ->exec($rsync_command1)
      ->exec($rsync_command2)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Could not rsync files from BLT into your repository.");
    }
  }

  /**
   * Convert path.
   */
  protected function convertWindowsPathToCygwinPath($path) {
    return str_replace('\\', '/', preg_replace('/([A-Z]):/i', '/cygdrive/$1', $path));
  }

  /**
   * Sets project.name using the directory name of repo.root.
   */
  protected function setProjectName() {
    $project_name = basename($this->getConfigValue('repo.root'));
    $project_yml = $this->getConfigValue('blt.config-files.project');
    $yamlWriter = new YamlWriter($project_yml);
    $project_config = $yamlWriter->getContents();
    $project_config['project']['machine_name'] = $project_name;
    $yamlWriter->write($project_config);
  }

}
