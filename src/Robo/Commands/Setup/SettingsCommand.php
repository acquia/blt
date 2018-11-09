<?php

namespace Acquia\Blt\Robo\Commands\Setup;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\RandomString;
use Acquia\Blt\Robo\Exceptions\BltException;
use function file_exists;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Defines commands in the "blt:init:settings" namespace.
 */
class SettingsCommand extends BltTasks {

  protected $defaultBehatLocalConfigFile;
  protected $projectBehatLocalConfigFile;

  /**
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    $this->defaultBehatLocalConfigFile = $this->getConfigValue('repo.root') . '/tests/behat/example.local.yml';
    $this->projectBehatLocalConfigFile = $this->getConfigValue('repo.root') . '/tests/behat/local.yml';
  }

  /**
   * Generates default settings files for Drupal and drush.
   *
   * @command blt:init:settings
   *
   * @aliases bis settings setup:settings
   */
  public function generateSiteConfigFiles() {
    if (!file_exists($this->getConfigValue('blt.config-files.local'))) {
      $result = $this->taskFilesystemStack()
        ->copy($this->getConfigValue('blt.config-files.example-local'), $this->getConfigValue('blt.config-files.local'))
        ->stopOnFail()
        ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
        ->run();

      if (!$result->wasSuccessful()) {
        $filepath = $this->getInspector()->getFs()->makePathRelative($this->getConfigValue('blt.config-files.local'), $this->getConfigValue('repo.root'));
        throw new BltException("Unable to create $filepath.");
      }
    }

    // Generate hash file in salt.txt.
    $this->hashSalt();

    $default_multisite_dir = $this->getConfigValue('docroot') . "/sites/default";
    $default_project_default_settings_file = "$default_multisite_dir/default.settings.php";

    $multisites = $this->getConfigValue('multisites');
    $initial_site = $this->getConfigValue('site');
    $current_site = $initial_site;

    foreach ($multisites as $multisite) {
      if ($current_site != $multisite) {
        $this->switchSiteContext($multisite);
        $current_site = $multisite;
      }

      // Generate settings.php.
      $multisite_dir = $this->getConfigValue('docroot') . "/sites/$multisite";
      $project_default_settings_file = "$multisite_dir/default.settings.php";
      $project_settings_file = "$multisite_dir/settings.php";

      // Generate local.settings.php.
      $blt_local_settings_file = $this->getConfigValue('blt.root') . '/settings/default.local.settings.php';
      $default_local_settings_file = "$multisite_dir/settings/default.local.settings.php";
      $project_local_settings_file = "$multisite_dir/settings/local.settings.php";

      // Generate local.drush.yml.
      $blt_local_drush_file = $this->getConfigValue('blt.root') . '/settings/default.local.drush.yml';
      $default_local_drush_file = "$multisite_dir/default.local.drush.yml";
      $project_local_drush_file = "$multisite_dir/local.drush.yml";

      $copy_map = [
        $blt_local_settings_file => $default_local_settings_file,
        $default_local_settings_file => $project_local_settings_file,
        $blt_local_drush_file => $default_local_drush_file,
        $default_local_drush_file => $project_local_drush_file,
      ];
      // Define an array of files that require property expansion.
      $expand_map = [
        $default_local_settings_file => $project_local_settings_file,
        $default_local_drush_file => $project_local_drush_file,
      ];

      // Only add the settings file if the default exists.
      if (file_exists($default_project_default_settings_file)) {
        $copy_map[$default_project_default_settings_file] = $project_default_settings_file;
        $copy_map[$project_default_settings_file] = $project_settings_file;
      }
      else {
        $this->logger->warning("No $default_project_default_settings_file file found.");
      }

      $task = $this->taskFilesystemStack()
        ->stopOnFail()
        ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
        ->chmod($multisite_dir, 0777);

      if (file_exists($project_settings_file)) {
        $task->chmod($project_settings_file, 0777);
      }

      // Copy files without overwriting.
      foreach ($copy_map as $from => $to) {
        if (!file_exists($to)) {
          $task->copy($from, $to);
        }
      }

      $result = $task->run();

      foreach ($expand_map as $from => $to) {
        $this->getConfig()->expandFileProperties($to);
      }

      if (!$result->wasSuccessful()) {
        throw new BltException("Unable to copy files settings files from BLT into your repository.");
      }

      $result = $this->taskWriteToFile($project_settings_file)
        ->appendUnlessMatches('#vendor/acquia/blt/settings/blt.settings.php#', 'require DRUPAL_ROOT . "/../vendor/acquia/blt/settings/blt.settings.php";' . "\n")
        ->append(TRUE)
        ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
        ->run();

      if (!$result->wasSuccessful()) {
        throw new BltException("Unable to modify $project_settings_file.");
      }

      $result = $this->taskFilesystemStack()
        ->chmod($project_settings_file, 0644)
        ->stopOnFail()
        ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
        ->run();

      if (!$result->wasSuccessful()) {
        $filepath = $this->getInspector()->getFs()->makePathRelative($project_settings_file, $this->getConfigValue('repo.root'));
        throw new BltException("Unable to set permissions on $project_settings_file.");
      }
    }

    if ($current_site != $initial_site) {
      $this->switchSiteContext($initial_site);
    }
  }

  /**
   * Generates tests/behat/local.yml file for executing Behat tests locally.
   *
   * @command tests:behat:init:config
   * @aliases tbic setup:behat
   *
   */
  public function behat() {
    $copy_map = [
      $this->getConfigValue('blt.root') . '/template/tests/behat/behat.yml' => $this->getConfigValue('repo.root') . '/tests/behat/behat.yml',
      $this->getConfigValue('blt.root') . '/template/tests/behat/example.local.yml' => $this->defaultBehatLocalConfigFile,
      $this->defaultBehatLocalConfigFile => $this->projectBehatLocalConfigFile,
    ];

    $task = $this->taskFilesystemStack()
      ->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE);

    // Copy files without overwriting.
    foreach ($copy_map as $from => $to) {
      if (file_exists($to)) {
        unset($copy_map[$from]);
      }
    }

    if ($copy_map) {
      $this->say("Generating Behat configuration files...");
      foreach ($copy_map as $from => $to) {
        $task->copy($from, $to);
      }
      $result = $task->run();
      foreach ($copy_map as $from => $to) {
        $this->getConfig()->expandFileProperties($to);
      }

      if (!$result->wasSuccessful()) {
        $filepath = $this->getInspector()->getFs()->makePathRelative($this->defaultBehatLocalConfigFile, $this->getConfigValue('repo.root'));
        throw new BltException("Unable to copy $filepath into your repository.");
      }
    }
  }

  /**
   * Installs BLT git hooks to local .git/hooks directory.
   *
   * @command blt:init:git-hooks
   * @aliases big setup:git-hooks
   */
  public function gitHooks() {
    foreach ($this->getConfigValue('git.hooks') as $hook => $path) {
      $this->installGitHook($hook);
    }
  }

  /**
   * Installs a given git hook.
   *
   * This symlinks the hook into the project's .git/hooks directory.
   *
   * @param string $hook
   *   The git hook to install, e.g., 'pre-commit'.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function installGitHook($hook) {
    $fs = new Filesystem();
    $project_hook_directory = $this->getConfigValue('repo.root') . "/.git/hooks";
    $project_hook = $project_hook_directory . "/$hook";
    if ($this->getConfigValue('git.hooks.' . $hook)) {
      $this->say("Installing $hook git hook...");
      $hook_source = $this->getConfigValue('git.hooks.' . $hook) . "/$hook";
      $path_to_hook_source = rtrim($fs->makePathRelative($hook_source, $project_hook_directory), '/');

      $result = $this->taskFilesystemStack()
        ->mkdir($this->getConfigValue('repo.root') . '/.git/hooks')
        ->remove($project_hook)
        ->symlink($path_to_hook_source, $project_hook)
        ->stopOnFail()
        ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
        ->run();

      if (!$result->wasSuccessful()) {
        throw new BltException("Unable to install $hook git hook.");
      }
    }
    else {
      if (file_exists($project_hook)) {
        $this->say("Removing disabled $hook git hook...");
        $result = $this->taskFilesystemStack()
          ->remove($project_hook)
          ->stopOnFail()
          ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
          ->run();

        if (!$result->wasSuccessful()) {
          throw new BltException("Unable to remove disabled $hook git hook");
        }
      }
      else {
        $this->say("Skipping installation of $hook git hook...");
      }
    }
  }

  /**
   * Writes a hash salt to ${repo.root}/salt.txt if one does not exist.
   *
   * @command drupal:hash-salt:init
   * @aliases dhsi setup:hash-salt
   *
   * @return int
   *   A CLI exit code.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function hashSalt() {
    $hash_salt_file = $this->getConfigValue('repo.root') . '/salt.txt';
    if (!file_exists($hash_salt_file)) {
      $this->say("Generating hash salt...");
      $result = $this->taskWriteToFile($hash_salt_file)
        ->line(RandomString::string(55))
        ->run();

      if (!$result->wasSuccessful()) {
        $filepath = $this->getInspector()->getFs()->makePathRelative($hash_salt_file, $this->getConfigValue('repo.root'));
        throw new BltException("Unable to write hash salt to $filepath.");
      }

      return $result->getExitCode();
    }
    else {
      $this->say("Hash salt already exists.");
      return 0;
    }
  }

  /**
   * Writes a deployment_identifier to ${repo.root}/deployment_identifier.
   *
   * @command drupal:deployment-identifier:init
   * @aliases ddii
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function createDeployId($options = ['id' => InputOption::VALUE_REQUIRED]) {
    if (!$options['id']) {
      $options['id'] = RandomString::string(8);
    }
    $deployment_identifier_file = $this->getConfigValue('repo.root') . '/deployment_identifier';
    $this->say("Generating deployment identifier...");
    $result = $this->taskWriteToFile($deployment_identifier_file)
      ->line($options['id'])
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      $filepath = $this->getInspector()->getFs()->makePathRelative($deployment_identifier_file, $this->getConfigValue('repo.root'));
      throw new BltException("Unable to write deployment identifier to $filepath.");
    }
  }

}
