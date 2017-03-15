<?php

namespace Acquia\Blt\Update;

use Acquia\Blt\Annotations\Update;

/**
 * Defines scripted updates for specific version deltas of BLT.
 *
 * Note that every update should be designed to execute against *any* version of
 * blt given that a dev version of BLT will execute all updates regardless of
 * recency.
 */
class Updates {

  /**
   * @var \Acquia\Blt\Update\Updater
   */
  protected $updater;

  /**
   * Updates constructor.
   *
   * @param \Acquia\Blt\Update\Updater $updater
   */
  public function __construct(Updater $updater) {
    $this->updater = $updater;
  }

  /**
   * 8.5.1.
   *
   * @Update(
   *   version = "8005001",
   *   description = "Removes deprecated features patch."
   * )
   */
  public function update_8005001() {
    $this->updater->removeComposerPatch("drupal/features",
      "https://www.drupal.org/files/issues/features-2808303-2.patch");
  }

  /**
   * 8.6.0.
   *
   * @Update(
   *   version = "8006000",
   *   description = "Moves configuration files to blt subdirectory. Removes .git/hooks symlink."
   * )
   */
  public function update_8006000() {
    // Move files to blt subdir.
    $this->updater->moveFile('project.yml', 'blt/project.yml', TRUE);
    $this->updater->moveFile('project.local.yml', 'blt/project.local.yml',
      TRUE);
    $this->updater->moveFile('example.project.local.yml',
      'blt/example.project.local.yml', TRUE);

    // Delete symlink to hooks directory. Individual git hooks are now symlinked, not the entire directory.
    $this->updater->deleteFile('.git/hooks');
    $this->updater->getOutput()
      ->writeln('.git/hooks was deleted. Please re-run setup:git-hooks to install git hooks locally.');

    $this->updater->removeComposerRepository('https://github.com/mortenson/composer-patches');
    $this->updater->removeComposerScript('post-create-project-cmd');

    // Change 'deploy' module key to 'prod'.
    // @see https://github.com/acquia/blt/pull/700.
    $project_config = $this->updater->getProjectConfig();
    if (!empty($project_config['modules']['deploy'])) {
      $project_config['modules']['prod'] = $project_config['modules']['deploy'];
      unset($project_config['modules']['deploy']);
    }

    $this->updater->getOutput()
      ->writeln("<comment>You MUST remove .travis.yml and re-initialize Travis CI support with `blt ci:travis:init`.</comment>");
  }

  /**
   * 8.6.2.
   *
   * @Update(
   *   version = "8006002",
   *   description = "Updates composer.json version constraints for Drupal.org."
   * )
   */
  public function update_8006002() {
    $composer_json = $this->updater->getComposerJson();
    $composer_json = DoPackagistConverter::convertComposerJson($composer_json);
    // This package is not compatible with D.O style version constraints.
    unset($composer_json['require']['drupal-composer/drupal-security-advisories']);
    $this->updater->writeComposerJson($composer_json);
  }

  /**
   * 8.5.4.
   *
   * @Update(
   *   version = "8006004",
   *   description = "Removes deprecated packages from composer.json."
   * )
   */
  public function update_8006004() {
    $composer_json = $this->updater->getComposerJson();
    $remove_packages = [
      'drupal/coder',
      'drupal-composer/drupal-security-advisories',
      'phing/phing',
      'phpunit/phpunit',
      'behat/mink-extension',
      'behat/mink-goutte-driver',
      'behat/mink-browserkit-driver',
    ];
    foreach ($remove_packages as $package) {
      unset($composer_json['require'][$package]);
      unset($composer_json['require-dev'][$package]);
    }
    $this->updater->writeComposerJson($composer_json);
  }

  /**
   * 8.6.6.
   *
   * @Update(
   *   version = "8006006",
   *   description = "Removes drush/drush from require-dev."
   * )
   */
  public function update_8006006() {
    $composer_json = $this->updater->getComposerJson();
    unset($composer_json['require-dev']['drush/drush']);
    $this->updater->writeComposerJson($composer_json);
  }

  /**
   * 8.6.7.
   *
   * @Update(
   *   version = "8006007",
   *   description = "Changes drupal scaffold excludes from associative to indexed array."
   * )
   */
  public function update_8006007() {
    $composer_json = $this->updater->getComposerJson();
    if (!empty($composer_json['extra']['drupal-scaffold']['excludes'])) {
      $composer_json['extra']['drupal-scaffold']['excludes'] = array_unique(array_values($composer_json['extra']['drupal-scaffold']['excludes']));
    }
    $this->updater->writeComposerJson($composer_json);
  }

  /**
   * 8.6.12.
   *
   * @Update(
   *   version = "8006012",
   *   description = "Removes lightning patch."
   * )
   */
  public function update_8006012() {
    $this->updater->removeComposerPatch("acquia/lightning",
      "https://www.drupal.org/files/issues/2836258-3-lightning-extension-autoload.patch");
  }

  /**
   * 8.6.15.
   *
   * @Update(
   *   version = "8006015",
   *   description = "Updating composer.json to use wikimedia composer-merge-plugin."
   * )
   */
  public function update_8006015() {
    $composer_include_json = $this->updater->getComposerIncludeJson();
    $composer_json = $this->updater->getComposerJson();

    // Remove deprecated config.
    unset($composer_json['extra']['blt']['composer-exclude-merge']);

    // Remove config that should only be defined in composer.include.json.
    unset($composer_json['extra']['enable-patching']);
    unset($composer_json['extra']['installer-paths']);

    // Remove packages from root composer.json that are already defined in BLT's composer.include.json with matching version.
    foreach ($composer_include_json['require'] as $package_name => $package_version) {
      if (array_key_exists($package_name, $composer_json['require']) && $package_version == $composer_json['require'][$package_name]) {
        unset($composer_json['require'][$package_name]);
      }
    }
    foreach ($composer_include_json['require-dev'] as $package_name => $package_version) {
      if (array_key_exists($package_name, $composer_json['require-dev']) && $package_version == $composer_json['require-dev'][$package_name]) {
        unset($composer_json['require-dev'][$package_name]);
      }
    }

    // Remove redundant config for drupal-scaffold.
    if (!empty($composer_json['extra']['drupal-scaffold']) && !empty($composer_include_json['extra']['drupal-scaffold']) &&
      $composer_json['extra']['drupal-scaffold'] == $composer_include_json['extra']['drupal-scaffold']) {
      unset($composer_json['extra']['drupal-scaffold']);
    }

    // Remove redundant config for autoload-dev.
    unset($composer_json['autoload-dev']['psr-4']['Drupal\\Tests\\PHPUnit\\']);
    if (empty($composer_json['autoload-dev']['psr-4'])) {
      unset($composer_json['autoload-dev']['psr-4']);
    }
    if (empty($composer_json['autoload-dev'])) {
      unset($composer_json['autoload-dev']);
    }

    // Remove redundant config for repositories.
    if (!empty($composer_json['repositories']['drupal']) &&
      $composer_json['repositories']['drupal'] == $composer_include_json['repositories']['drupal']) {
      unset($composer_json['repositories']['drupal']);
    }
    if (empty($composer_json['repositories'])) {
      unset($composer_json['repositories']);
    }

    if (!empty($composer_json['scripts'])) {
      foreach ($composer_include_json['scripts'] as $script_name => $script) {
        if (array_key_exists($script_name, $composer_json['scripts'])) {
          unset($composer_json['scripts'][$script_name]);
        }
      }
      if (empty($composer_json['scripts'])) {
        unset($composer_json['scripts']);
      }
    }

    // Set wikimedia/composer-merge-plugin config.
    $template_composer_json = $this->updater->getTemplateComposerJson();
    $composer_json['extra']['merge-plugin'] = $template_composer_json['extra']['merge-plugin'];

    // Write to file.
    $this->updater->writeComposerJson($composer_json);

    $messages = [
      'After this, BLT will no longer modify your composer.json automatically!',
      'Default composer.json values from BLT are now merged into your root composer.json via the wikimedia/composer-merge-plugin. You may override any default value provided by BLT by setting the same key in your root composer.json. BLT will never revert your overrides, so you are responsible for maintaining them. Please review your composer.json file carefully.',
    ];
    $formattedBlock = $this->updater->getFormatter()->formatBlock($messages, 'ice', TRUE);
    $this->updater->getOutput()->writeln($formattedBlock);
  }
}
