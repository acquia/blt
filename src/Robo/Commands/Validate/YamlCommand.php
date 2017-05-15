<?php

namespace Acquia\Blt\Robo\Commands\Validate;

use Acquia\Blt\Robo\BltTasks;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "validate:yaml*" namespace.
 */
class YamlCommand extends BltTasks {

  /**
   * @command validate:yaml
   */
  public function lint() {
    $this->say("Validating yaml syntax for all custom modules and exported config...");

    $filesets_to_lint = $this->getConfigValue('validate.yaml.filesets');
    $bin = $this->getConfigValue('composer.bin');
    $command = "'$bin/yaml-cli' lint '%s'";
    $result = $this->executeCommandAgainstFilesets($filesets_to_lint, $command);

    return $result;
  }
}
