<?php

namespace Acquia\Blt\Robo\Hooks;

use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Acquia\Blt\Robo\Inspector\InspectorAwareInterface;
use Acquia\Blt\Robo\Inspector\InspectorAwareTrait;
use Consolidation\AnnotatedCommand\AnnotationData;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\IOAwareInterface;
use Robo\Tasks;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 */
class InteractHook extends Tasks implements IOAwareInterface, ConfigAwareInterface, InspectorAwareInterface, LoggerAwareInterface {

  use ConfigAwareTrait;
  use InspectorAwareTrait;
  use LoggerAwareTrait;
  use IO;

  /**
   *
   */
  public function setInput(InputInterface $input) {
    $this->input = $input;
  }

  /**
   *
   */
  public function setOutput(OutputInterface $output) {
    $this->output = $output;
  }

  /**
   * @hook interact @interactInstallDrupal
   */
  public function interactInstallDrupal(
    InputInterface $input,
    OutputInterface $output,
    AnnotationData $annotationData
  ) {
    if (!$this->Inspector->isDrupalInstalled()) {
      $this->logger->warning('Drupal is not installed.');
      $confirm = $this->confirm("Do you want to install Drupal?");
      if ($confirm) {
        $bin = $this->getConfigValue('composer.bin');
        $this->taskExec("$bin/blt setup:drupal:install")
          ->dir($this->getConfigValue('repo.root'))
          ->run();
      }
    }
  }

  /**
   * @hook interact @interactConfigureBehat
   */
  public function interactConfigureBehat(
    InputInterface $input,
    OutputInterface $output,
    AnnotationData $annotationData
  ) {
    if (!$this->Inspector->isBehatConfigured()) {
      $this->logger->warning('Behat is not configured.');
      $confirm = $this->confirm("Do you want configure Behat.");
      if ($confirm) {
        $bin = $this->getConfigValue('composer.bin');
        $this->taskExec("$bin/blt setup:behat")
          ->dir($this->getConfigValue('repo.root'))
          ->run();
      }
    }
  }

}
