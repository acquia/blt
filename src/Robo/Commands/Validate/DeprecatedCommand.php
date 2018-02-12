<?php

namespace Acquia\Blt\Robo\Commands\Validate;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "tests:php:sniff:deprecated*" namespace.
 */
class DeprecatedCommand extends BltTasks {

  /**
   * Detects usage of deprecated custom code.
   *
   * @command tests:php:sniff:deprecated
   *
   * @aliases tpsd deprecated
   */
  public function detect() {
    $this->say("Checking for deprecated code...");
    $task = $this->taskExecStack()
      ->dir($this->getConfigValue('repo.root'));

    $dirs = [
      'blt/src',
      'docroot/modules/custom',
      'tests/phpunit',
      'tests/behat',
    ];

    foreach ($dirs as $dir) {
      if (file_exists($dir)) {
        $bin = $this->getConfigValue('composer.bin');
        $command = "$bin/deprecation-detector check '$dir'";
        if ($this->output()->isVerbose()) {
          $command .= " --verbose";
        }
        $task->exec($command);
      }
    }
    $task->run();
    // We intentionally do not fail for deprecated code.
  }

}
