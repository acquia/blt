<?php

namespace Acquia\Blt\Robo\Commands\Source;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\RandomString;
use Acquia\Blt\Robo\Config\ConfigInitializer;
use Acquia\Blt\Robo\Exceptions\BltException;
use Acquia\Drupal\RecommendedSettings\Drush\Commands\SettingsDrushCommands;
use Acquia\Drupal\RecommendedSettings\Exceptions\SettingsException;
use Acquia\Drupal\RecommendedSettings\Settings;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\ResultData;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Defines commands in the "blt:init:settings" namespace.
 */
class SettingsCommand extends BltTasks {

  /**
   * Generates default settings files for Drupal and drush.
   *
   * @command source:build:settings
   *
   * @aliases blt:init:settings bis settings setup:settings
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function generateSiteConfigFiles() {
    $this->generateLocalConfigFile();

    // Reload config.
    $config_initializer = new ConfigInitializer($this->getConfigValue('repo.root'), $this->input());
    $config_initializer->setSite($this->getConfig()->get('site'));
    $new_config = $config_initializer->initialize();

    $this->getConfig()->replace($new_config->export());

    $multisites = $this->getConfigValue('multisites');
    $initial_site = $this->getConfigValue('site');
    $current_site = $initial_site;

    $this->logger->debug("Multisites found: " . implode(',', $multisites));
    $this->logger->debug("Initial site: $initial_site");

    foreach ($multisites as $multisite) {
      if ($current_site != $multisite) {
        $this->switchSiteContext($multisite);
        $current_site = $multisite;

        $result = $this->taskDrush()
          ->drush(SettingsDrushCommands::SETTINGS_COMMAND)
          ->uri($multisite)
          ->run();
        if ($result->getExitCode() == ResultData::EXITCODE_ERROR) {
          $this->io->error($result->getMessage());
        }

      }
      $multisite_dir = $this->getConfigValue('docroot') . "/sites/$multisite";

      // Generate local.drush.yml.
      $blt_local_drush_file = $this->getConfigValue('blt.root') . '/settings/default.local.drush.yml';
      $default_local_drush_file = "$multisite_dir/default.local.drush.yml";
      $project_local_drush_file = "$multisite_dir/local.drush.yml";

      $copy_map = [
        $blt_local_drush_file => $default_local_drush_file,
        $default_local_drush_file => $project_local_drush_file,
      ];
      // Define an array of files that require property expansion.
      $expand_map = [
        $default_local_drush_file => $project_local_drush_file,
      ];

      $task = $this->taskFilesystemStack()
        ->stopOnFail()
        ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
        ->chmod($multisite_dir, 0777);

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
    }

    if ($current_site != $initial_site) {
      $this->switchSiteContext($initial_site);
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
        // phpcs:ignore
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

  /**
   * Generates local.blt.yml from example.local.blt.yml.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  private function generateLocalConfigFile() {
    $localConfigFile = $this->getConfigValue('blt.config-files.local');
    $exampleLocalConfigFile = $this->getConfigValue('blt.config-files.example-local');
    $localConfigFilepath = $this->getInspector()
      ->getFs()
      ->makePathRelative($localConfigFile, $this->getConfigValue('repo.root'));
    $exampleLocalConfigFilepath = $this->getInspector()
      ->getFs()
      ->makePathRelative($exampleLocalConfigFile, $this->getConfigValue('repo.root'));

    if (file_exists($localConfigFile)) {
      // Don't overwrite an existing local.blt.yml.
      return;
    }

    if (!file_exists($exampleLocalConfigFile)) {
      $this->say("Could not find $exampleLocalConfigFilepath. Create and commit this file if you'd like to automatically generate $localConfigFilepath based on this template.");
      return;
    }

    $result = $this->taskFilesystemStack()
      ->copy($exampleLocalConfigFile, $localConfigFile)
      ->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to create $localConfigFilepath.");
    }
  }

}
