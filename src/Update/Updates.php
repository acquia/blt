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
}
