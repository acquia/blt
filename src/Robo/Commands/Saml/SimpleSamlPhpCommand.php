<?php

namespace Acquia\Blt\Robo\Commands\Saml;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Console\Helper\FormatterHelper;

/**
 * Defines commands in the "simplesamlphp:*" namespace.
 */
class SimpleSamlPhpCommand extends BltTasks {

  protected $bltRoot;
  protected $repoRoot;
  /**
   * @var \Symfony\Component\Console\Helper\FormatterHelper
   */
  protected $formatter;

  /**
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    $this->bltRoot = $this->getConfigValue('blt.root');
    $this->repoRoot = $this->getConfigValue('repo.root');
    $this->formatter = new FormatterHelper();
  }

  /**
   * Initializes SimpleSAMLphp for project.
   *
   * @command simplesamlphp:init
   */
  public function initializeSimpleSamlPhp() {
    if (!$this->getInspector()->isSimpleSamlPhpInstalled()) {
      $this->requireModule();
      $this->initializeConfig();
      $this->setSimpleSamlPhpInstalled();
      $this->symlinkDocrootToLibDir();
    }
    else {
      $this->say('SimpleSAMLphp has already been initialized by BLT.');
    }
    $this->outputCompleteSetupInstructions();
  }

  /**
   * Adds simplesamlphp_auth as a dependency.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function requireModule() {
    $this->say('Adding SimpleSAMLphp Auth module as a dependency...');

    $package_options = [
      'package_name' => 'drupal/simplesamlphp_auth',
      'package_version' => '^3.0',
    ];
    $this->invokeCommand('composer:require', $package_options);
  }

  /**
   * Copies configuration templates from SimpleSamlPHP to the repo root.
   *
   * @command simplesamlphp:config:init
   */
  protected function initializeConfig() {
    $destinationDirectory = "{$this->repoRoot}/simplesamlphp/config";

    $this->say("Copying config files to ${destinationDirectory}...");
    $result = $this->taskFileSystemStack()
      ->copy("{$this->repoRoot}/vendor/simplesamlphp/simplesamlphp/config-templates/authsources.php", "${destinationDirectory}/authsources.php", TRUE)
      ->copy("{$this->repoRoot}/vendor/simplesamlphp/simplesamlphp/config-templates/config.php", "${destinationDirectory}/config.php", TRUE)
      ->copy("{$this->bltRoot}/scripts/simplesamlphp/acquia_config.php", "${destinationDirectory}/acquia_config.php", TRUE)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to copy SimpleSamlPhp config files.");
    }

    $config_file = "{$this->repoRoot}/simplesamlphp/config/config.php";
    $result = $this->taskWriteToFile($config_file)
      ->text("include 'acquia_config.php';")
      ->append()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Unable modify $config_file.");
    }

    $this->say("Copying config files to {$this->repoRoot}/simplesamlphp/metadata...");
    $result = $this->taskFileSystemStack()
      ->copy("{$this->repoRoot}/vendor/simplesamlphp/simplesamlphp/metadata-templates/saml20-idp-remote.php", "{$this->repoRoot}/simplesamlphp/metadata/saml20-idp-remote.php", TRUE)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to initialize SimpleSamlPhp configuration.");
    }
  }

  /**
   * Copies custom config files to SimpleSamlPHP in deploy artifact.
   *
   * @command simplesamlphp:deploy:config
   */
  public function simpleSamlPhpDeployConfig() {
    $this->say('Copying config files to the appropriate place in simplesamlphp library in the deploy artifact...');
    $result = $this->taskCopyDir(["{$this->repoRoot}/simplesamlphp" => "{$this->repoRoot}/deploy/vendor/simplesamlphp/simplesamlphp"])
      ->overwrite(TRUE)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to copy SimpleSamlPhp files into deployment artifact.");
    }

    $result = $this->taskFileSystemStack()
      ->copy("{$this->bltRoot}/scripts/simplesamlphp/gitignore.txt", "{$this->repoRoot}/deploy/vendor/simplesamlphp/simplesamlphp/.gitignore", TRUE)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to copy SimpleSamlPhp .gitignore into deployment artifact.");
    }
  }

  /**
   * Sets value in project.yml to let targets know simplesamlphp is installed.
   */
  protected function setSimpleSamlPhpInstalled() {
    $composerBin = $this->getConfigValue('composer.bin');
    $project_yml = $this->getConfigValue('blt.config-files.project');
    $this->say("Updating ${project_yml}...");

    $result = $this->taskExec("{$composerBin}/yaml-cli update:value")
      ->arg($project_yml)
      ->arg('simplesamlphp')
      ->arg('TRUE')
      ->printOutput(TRUE)
      ->detectInteractive()
      ->dir($this->getConfigValue('repo.root'))
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to update $project_yml.");
    }
  }

  /**
   * Creates a symlink from the docroot to the web accessible library dir.
   */
  protected function symlinkDocrootToLibDir() {
    $docroot = $this->getConfigValue('docroot');

    $this->say("Creating a symbolic link from ${docroot}/simplesaml to web accessible directory in the simplesamlphp library...");
    $result = $this->taskFileSystemStack()
      ->symlink("../vendor/simplesamlphp/simplesamlphp/www", "${docroot}/simplesaml")
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Unable create symlink.");
    }
  }

  /**
   * Copies customized config files into vendored SimpleSamlPHP.
   *
   * @command simplesamlphp:build:config
   */
  public function simpleSamlPhpBuildConfig() {
    $this->say('Copying config files to the appropriate place in simplesamlphp library...');
    $result = $this->taskCopyDir(["{$this->repoRoot}/simplesamlphp" => "{$this->repoRoot}/vendor/simplesamlphp/simplesamlphp"])
      ->overwrite(TRUE)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to copy configuration into SimpleSamlPhp.");
    }

    $result = $this->taskFileSystemStack()
      ->copy("{$this->bltRoot}/scripts/simplesamlphp/gitignore.txt", "{$this->repoRoot}/vendor/simplesamlphp/simplesamlphp/.gitignore", TRUE)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to copy .gitignore into SimpleSamlPhp.");
    }
  }

  /**
   * Outputs a message to edit the new config files.
   */
  protected function outputCompleteSetupInstructions() {
    $docroot = $this->getConfigValue('docroot');
    $instructions = [
      'To complete the setup you must manually modify several files:',
      '',
      "* ${docroot}/.htaccess",
      "* {$this->repoRoot}/simplesamlphp/config/acquia_config.php",
      "* {$this->repoRoot}/simplesamlphp/config/authsources.php",
      "* {$this->repoRoot}/simplesamlphp/metadata/saml20-idp-remote.php",
      '',
      'After editing these files execute the following command to copy the modified files to the correct location in the SimpleSAMLphp library:',
      '',
      "'blt simplesamlphp:build:config'",
      '',
      "See http://blt.readthedocs.io/en/latest/readme/simplesamlphp-setup/ for details on how to modify the files.",
    ];
    $formattedBlock = $this->formatter->formatBlock($instructions, 'comment', TRUE);
    $this->writeln($formattedBlock);
  }

}
