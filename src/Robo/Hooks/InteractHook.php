<?php

namespace Acquia\Blt\Robo\Hooks;

use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Acquia\Blt\Robo\Inspector\InspectorAwareInterface;
use Acquia\Blt\Robo\Inspector\InspectorAwareTrait;
use Acquia\Blt\Robo\Wizards\SetupWizard;
use Acquia\Blt\Robo\Wizards\TestsWizard;
use Consolidation\AnnotatedCommand\AnnotationData;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\IOAwareInterface;
use Robo\Tasks;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This class defines hooks that provide user interaction.
 *
 * These hooks typically use a Wizard to evaluate the validity of config or
 * state and guide the user toward resolving issues.
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
    /** @var SetupWizard $setup_wizard */
    $setup_wizard = $this->getContainer()->get(SetupWizard::class);
    $setup_wizard->wizardInstallDrupal();
  }

  /**
   * @hook interact @interactConfigureBehat
   */
  public function interactConfigureBehat(
    InputInterface $input,
    OutputInterface $output,
    AnnotationData $annotationData
  ) {
    /** @var TestsWizard $tests_wizard */
    $tests_wizard = $this->getContainer()->get(TestsWizard::class);
    $tests_wizard->wizardConfigureBehat();
  }

  /**
   * @hook interact @interactLaunchPhpWebServer
   */
  public function interactLaunchPhpWebServer() {
    if ($this->getConfigValue('behat.run-server')) {
      $this->logger->info("Using 'drush runserver' for tests.");
      $server_url = $this->getConfigValue('behat.server-url');
      // $this->getConfig()->set('project.local.uri', $server_url);
      $this->logger->info("Running server at $server_url");
      $this->getContainer()->get('executor')->killProcessByName('runserver');
      $this->getContainer()->get('executor')->killProcessByPort(8888);
      $composer_bin = $this->getConfigValue('composer.bin');
      $this->taskExec("$composer_bin/drush runserver $server_url > /dev/null")
        ->dir($this->getConfigValue('docroot'))
        ->background(true)
        ->printOutput(false)
        ->run();
      $this->getContainer()->get('executor')->waitForUrlAvailable($server_url);
    }
  }

}
