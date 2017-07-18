<?php

namespace Acquia\Blt\Robo\Hooks;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Wizards\SetupWizard;
use Acquia\Blt\Robo\Wizards\TestsWizard;
use Consolidation\AnnotatedCommand\AnnotationData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This class defines hooks that provide user interaction.
 *
 * These hooks typically use a Wizard to evaluate the validity of config or
 * state and guide the user toward resolving issues.
 */
class InteractHook extends BltTasks {

  /**
   * Sets $this->input.
   */
  public function setInput(InputInterface $input) {
    $this->input = $input;
  }

  /**
   * Sets $this->output.
   */
  public function setOutput(OutputInterface $output) {
    $this->output = $output;
  }

  /**
   * Runs wizard for generating settings files.
   *
   * @hook interact @interactGenerateSettingsFiles
   */
  public function interactGenerateSettingsFiles(
    InputInterface $input,
    OutputInterface $output,
    AnnotationData $annotationData
  ) {
    /** @var \Acquia\Blt\Robo\Wizards\SetupWizard $setup_wizard */
    $setup_wizard = $this->getContainer()->get(SetupWizard::class);
    $setup_wizard->wizardGenerateSettingsFiles();
  }

  /**
   * Runs wizard for installing Drupal.
   *
   * @hook interact @interactInstallDrupal
   */
  public function interactInstallDrupal(
    InputInterface $input,
    OutputInterface $output,
    AnnotationData $annotationData
  ) {
    /** @var \Acquia\Blt\Robo\Wizards\SetupWizard $setup_wizard */
    $setup_wizard = $this->getContainer()->get(SetupWizard::class);
    $setup_wizard->wizardInstallDrupal();
  }

  /**
   * Runs wizard for configuring Behat.
   *
   * @hook interact @interactConfigureBehat
   */
  public function interactConfigureBehat(
    InputInterface $input,
    OutputInterface $output,
    AnnotationData $annotationData
  ) {
    /** @var \Acquia\Blt\Robo\Wizards\TestsWizard $tests_wizard */
    $tests_wizard = $this->getContainer()->get(TestsWizard::class);
    $tests_wizard->wizardConfigureBehat();
  }

  /**
   * Executes outstanding updates.
   *
   * @hook interact *
   */
  public function interactExecuteUpdates(
    InputInterface $input,
    OutputInterface $output,
    AnnotationData $annotationData
  ) {
    if ($this->invokeDepth == 0 && $input->getFirstArgument() != 'update' && !$this->getInspector()->isSchemaVersionUpToDate()) {
      $this->logger->warning("Your BLT schema is out of date.");
      if (!$input->isInteractive()) {
        $this->logger->warning("Run `blt update` to update it.");
      }
      $confirm = $this->confirm("Would you like to run outstanding updates?");
      if ($confirm) {
        $this->invokeCommand('update');
      }
    }
  }

}
