<?php

namespace Acquia\Blt\Robo\Commands\Generate;

use Acquia\Blt\Robo\BltTasks;
use Drupal\Component\Uuid\Php;

/**
 * Defines commands in the recipes:config:init:splits namespace.
 */
class ConfigSplitCommand extends BltTasks {

  /**
   * An instance of the Php UUID generator used by the Drupal UUID service.
   *
   * @var \Drupal\Component\Uuid\Php
   */
  protected $uuidGenerator;

  /**
   * An instance of the Twig template environment.
   *
   * @var \Twig_Environment
   */
  protected $twig;

  /**
   * The directory where the default configuration is stored.
   *
   * @var string
   */
  protected $configSyncDir;

  /**
   * The base directory for individual config_split configuration files.
   *
   * @var string
   */
  protected $configSplitDir;

  /**
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    $this->uuidGenerator = new Php();
    $template_dir = $this->getConfigValue('blt.root') . '/scripts/config-split/templates';
    $loader = new \Twig_Loader_Filesystem($template_dir);
    $this->twig = new \Twig_Environment($loader);
    $docroot = $this->getConfigValue('docroot');
    $this->configSyncDir = $docroot . '/' . $this->getConfigValue('cm.core.dirs.sync.path');
    $this->configSplitDir = $docroot . '/' . $this->getConfigValue('cm.core.path') . '/envs';
  }

  /**
   * Generates empty config_split splits for the selected environments.
   *
   * @command recipes:config:init:splits
   *
   * @aliases rcis splits
   */
  public function generateConfigSplits() {
    $this->say("This command will generate configuration and directories for the following environment based splits: Local, CI, Dev, Stage, and Prod.");

    $default_splits = ['Local', 'CI', 'Dev', 'Stage', 'Prod'];
    foreach ($default_splits as $split) {
      $this->createSplitConfig($split);
    }
  }

  /**
   * Create a config_split configuration and directory for the given split.
   *
   * @param string $name
   *   The name of the split to create.
   */
  protected function createSplitConfig($name) {
    $id = strtolower($name);
    $split_config_file = $this->configSyncDir . "/config_split.config_split.{$id}.yml";
    if (file_exists($split_config_file)) {
      $this->say("The config_split file for $name already exists. Skipping.");
    }
    else {
      $uuid = $this->uuidGenerator->generate();
      $config = $this->twig->render('config_split.config_split.env.yml.twig', [
        'uuid' => $uuid,
        'name' => $name,
        'id' => $id,
      ]);
      $this->createSplitDir($name);
      $this->writeSplitConfig($split_config_file, $config);
    }
  }

  /**
   * Creates the config directory for the given config_split.
   *
   * @param string $split
   *   The name of the split.
   */
  protected function createSplitDir($split) {
    $split_dir = $this->configSplitDir . '/' . strtolower($split);
    $result = $this->taskFilesystemStack()
      ->mkdir($split_dir)
      ->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to create $split_dir.");
    }
    if (!file_exists($split_dir . '/README.md')) {
      $readme = $this->twig->render('README.md.twig', [
        'name' => $split,
      ]);
      file_put_contents($split_dir . '/README.md', $readme);
    }
  }

  /**
   * Write the config_split configuration YAML file in the given directory.
   *
   * @param string $file_path
   *   The path where the file should be written.
   * @param string $config
   *   The config file contents.
   */
  protected function writeSplitConfig($file_path, $config) {
    $result = $this->taskWriteToFile($file_path)
      ->text($config)
      ->run();
    if (!$result) {
      throw new BltException("Unable to write $file_path.");
    }
  }

}
