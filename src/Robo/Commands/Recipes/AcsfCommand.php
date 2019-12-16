<?php

namespace Acquia\Blt\Robo\Commands\Recipes;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\YamlMunge;
use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "recipes:acsf" namespace.
 */
class AcsfCommand extends BltTasks {

  /**
   * Prints information about the command.
   */
  public function printPreamble() {
    $this->logger->notice("This command will initialize support for Acquia Cloud Site Factory by performing the following tasks:");
    $this->logger->notice("  * Adding drupal/acsf and acquia/acsf-tools the require array in your composer.json file.");
    $this->logger->notice("  * Executing the `acsf-init` command, provided by the drupal/acsf module.");
    $this->logger->notice("  * Adding default factory-hooks to your application.");
    $this->logger->notice("  * Adding `acsf` to `modules.local.uninstall` in your blt.yml");
    $this->logger->notice("");
    $this->logger->notice("Note that the default version of PHP on ACSF is generally not the same as Acquia Cloud.");
    $this->logger->notice("You may wish to adjust the PHP version of your local environment and CI tools to match.");
    $this->logger->notice("");
    $this->logger->notice("For more information, see:");
    $this->logger->notice("<comment>https://docs.acquia.com/blt/tech-architect/acsf-setup/</comment>");
  }

  /**
   * Initializes ACSF support for project.
   *
   * @command recipes:acsf:init:all
   *
   * @aliases acsf acsf:init
   * @options acsf-version
   */
  public function acsfInitialize($options = ['acsf-version' => '^2.47.0']) {
    $this->printPreamble();
    $this->acsfHooksInitialize();
    $this->acsfComposerInitialize();
    $this->say('Adding acsf module as a dependency...');
    $package_options = [
      'package_name' => 'drupal/acsf',
      'package_version' => $options['acsf-version'],
    ];
    $this->invokeCommand('internal:composer:require', $package_options);
    $this->say("In the future, you may pass in a custom value for acsf-version to override the default version, e.g., blt recipes:acsf:init:all --acsf-version='8.1.x-dev'");
    $this->acsfDrushInitialize();
    $this->say('Adding acsf-tools drush module as a dependency...');
    $package_options = [
      'package_name' => 'acquia/acsf-tools',
      'package_version' => 'dev-9.x-dev',
    ];
    $this->invokeCommand('internal:composer:require', $package_options);
    $this->say('<comment>ACSF Tools has been added. Some post-install configuration is necessary.</comment>');
    $this->say('<comment>See /drush/Commands/acsf_tools/README.md. </comment>');
    $this->say('<info>ACSF was successfully initialized.</info>');
    $this->say('Adding nedsbeds/profile_split_enable module as a dependency...');
    $package_options = [
      'package_name' => 'nedsbeds/profile_split_enable',
      'package_version' => '^1.0',
    ];
    $this->invokeCommand('internal:composer:require', $package_options);
    $this->say('<comment>nedsbeds/profile_split_enable module has been added.</comment>');
    $this->say('<comment>Enable the module and setup profile splits to utilize.</comment>');
    $project_yml = $this->getConfigValue('blt.config-files.project');
    $project_config = YamlMunge::parseFile($project_yml);
    if (!empty($project_config['modules'])) {
      $project_config['modules']['local']['uninstall'][] = 'acsf';
    }
    YamlMunge::writeFile($project_yml, $project_config);
  }

  /**
   * Ensure that ACSF-modified assets don't get overridden.
   */
  public function acsfComposerInitialize() {
    // .htaccess will be patched, excluding from further updates.
    $composer_filepath = $this->getConfigValue('repo.root') . '/composer.json';
    $composer_contents = json_decode(file_get_contents($composer_filepath));
    // Drupal Scaffold version (deprecate in Drupal 8.8, remove in Drupal 9).
    if (!property_exists($composer_contents->extra->{'drupal-scaffold'}, 'excludes') || !in_array('.htaccess', $composer_contents->extra->{'drupal-scaffold'}->excludes)) {
      $composer_contents->extra->{'drupal-scaffold'}->excludes[] = '.htaccess';
    }
    // Composer Scaffold version (supported as of Drupal 8.8).
    if (!property_exists($composer_contents->extra->{'drupal-scaffold'}, 'file-mapping') || !property_exists($composer_contents->extra->{'drupal-scaffold'}->{'file-mapping'}, '[web-root]/.htaccess')) {
      $composer_contents->extra->{'drupal-scaffold'}->{'file-mapping'} = new \stdClass();
      $composer_contents->extra->{'drupal-scaffold'}->{'file-mapping'}->{'[web-root]/.htaccess'} = FALSE;
      $composer_contents->extra->{'drupal-scaffold'}->{'gitignore'} = FALSE;
    }
    file_put_contents($composer_filepath, json_encode($composer_contents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
  }

  /**
   * Refreshes the ACSF settings and hook files.
   *
   * @command recipes:acsf:init:drush
   *
   * @aliases raid acsf:init:drush
   */
  public function acsfDrushInitialize() {
    $this->say('Executing initialization command provided acsf module...');
    $acsf_include = $this->getConfigValue('docroot') . '/modules/contrib/acsf/acsf_init';
    $result = $this->taskExecStack()
      ->exec($this->getConfigValue('repo.root') . "/vendor/bin/drush acsf-init --include=\"$acsf_include\" --root=\"{$this->getConfigValue('docroot')}\" -y")
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to copy ACSF scripts.");
    }

    return $result;
  }

  /**
   * Creates "factory-hooks/" directory in project's repo root.
   *
   * @command recipes:acsf:init:hooks
   * @aliases raih
   */
  public function acsfHooksInitialize() {
    $defaultAcsfHooks = $this->getConfigValue('blt.root') . '/scripts/factory-hooks';
    $projectAcsfHooks = $this->getConfigValue('repo.root') . '/factory-hooks';

    $result = $this->taskCopyDir([$defaultAcsfHooks => $projectAcsfHooks])
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to copy ACSF scripts.");
    }

    $this->say('New "factory-hooks/" directory created in repo root. Please commit this to your project.');

    return $result;
  }

  /**
   * Download drush 8 binary.
   *
   * @param string $destination
   *   Download destination.
   */
  protected function downloadDrush8($destination) {
    $file = fopen($destination, 'w');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,
      'https://github.com/drush-ops/drush/releases/download/8.1.15/drush.phar');
    curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_FILE, $file);
    curl_exec($ch);
    curl_close($ch);
    fclose($file);
    $this->_chmod($destination, 0755);
  }

}
