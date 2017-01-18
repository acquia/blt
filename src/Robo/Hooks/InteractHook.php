<?php

namespace Acquia\Blt\Robo\Hooks;

use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Acquia\Blt\Robo\LocalEnvironment\LocalEnvironmentInterface;
use Acquia\Blt\Robo\LocalEnvironment\LocalEnvironmentTrait;
use Consolidation\AnnotatedCommand\AnnotationData;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\IOAwareInterface;
use Robo\Tasks;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InteractHook extends Tasks implements IOAwareInterface, ConfigAwareInterface, LocalEnvironmentInterface {

  use ConfigAwareTrait;
  use LocalEnvironmentTrait;
  use IO;

  public function setInput(InputInterface $input) {
    $this->input = $input;
  }

  public function setOutput(OutputInterface $output) {
    $this->output = $output;
  }

  /**
   * @hook interact @wizardInstallDrupal
   */
  public function wizardInstallDrupal(InputInterface $input, OutputInterface $output, AnnotationData $annotationData) {
    if (!$this->localEnvironment->drupalIsInstalled()) {
      $confirm = $this->confirm("Do you want to install Drupal?");
      if ($confirm) {
        $bin = $this->getConfigValue('composer.bin');
        $this->taskExec("$bin/blt setup:drupal:install")
          ->dir($this->getConfigValue('repo.root'))
          ->run();
      }
    }
  }

}
