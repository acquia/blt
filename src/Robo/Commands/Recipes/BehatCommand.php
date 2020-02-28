<?php

namespace Acquia\Blt\Robo\Commands\Recipes;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "recipes:behat:*" namespace.
 */
class BehatCommand extends BltTasks {

  /**
   * Generates example files for writing custom Behat tests.
   *
   * @command recipes:behat:init
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function init() {
    $source = $this->getConfigValue('blt.root') . '/scripts/behat';
    $dest = $this->getConfigValue('repo.root') . '/tests/behat';
    $result = $this->taskCopyDir([$source => $dest])
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Could not copy example files into the repository root.");
    }

    $packages = [
      'behat/behat' => '^3.1',
      'behat/gherkin' => '^4.6.1',
      'bex/behat-screenshot' => '^1.2',
      'dmore/behat-chrome-extension' => '^1.0.0',
      'drupal/drupal-extension' => '~3.2',
      'jarnaiz/behat-junit-formatter' => '^1.3.2',
    ];

    foreach ($packages as $package_name => $package_version) {
      $package_options = [
        'package_name' => $package_name,
        'package_version' => $package_version,
        ['dev' => TRUE],
      ];
      $this->invokeCommand('internal:composer:require', $package_options);
    }

    $this->say("<info>Example Behat tests were copied into your application.</info>");
  }

}
