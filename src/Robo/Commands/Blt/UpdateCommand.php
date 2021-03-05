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
use Webmozart\PathUtil\Path;

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
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    $this->updater = $this->getContainer()->get('updater');
    $this->currentSchemaVersion = $this->getInspector()->getCurrentSchemaVersion();
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
    $this->initializeBlt();
    $this->setProjectName();
    $this->initGitignore();
    // Invoke command instead of calling method to ensure hooks run.
    $this->invokeCommand('internal:create-project:init-repo');
    $this->displayArt();
    $this->yell("BLT has been added to your project.");
    $this->say("Please continue by following the \"Adding BLT to an existing project\" instructions:");
    $this->say("<comment>https://docs.acquia.com/blt/install/adding-to-project/</comment>");
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
    $starting_version = $options['since'] ?: $this->currentSchemaVersion;
    if ($this->executeSchemaUpdates($starting_version)) {
      $this->updateSchemaVersionFile();
    }
    $this->invokeCommand('blt:init:shell-alias');
  }

  /**
   * Creates or appends to .gitignore with BLT-specific files.
   */
  public function initGitignore() {
    $gitignore = [];
    $gitignore_file = Path::join($this->getConfigValue('repo.root'), '.gitignore');
    if (file_exists($gitignore_file)) {
      $gitignore = file($gitignore_file, FILE_IGNORE_NEW_LINES);
    }
    $blt_files = [
      'local.settings.php',
      'local.drush.yml',
      'local.site.yml',
      'local.services.yml',
      '*.local',
      'local.blt.yml',
      'deployment_identifier',
      '/travis_wait*',
      '/files-private',
      '.phpcs-cache',
    ];
    foreach ($blt_files as $blt_file) {
      if (!in_array($blt_file, $gitignore)) {
        $gitignore[] = $blt_file;
      }
    }
    file_put_contents($gitignore_file, implode(PHP_EOL, $gitignore));
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
