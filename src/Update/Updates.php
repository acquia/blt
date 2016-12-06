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
   * @var \Acquia\Blt\Update\Updater*/
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
   * @Update(
   *   version = "8.5.1",
   *   description = "Removes deprecated features patch."
   * )
   */
  public function update_851() {
    $this->updater->removeComposerPatch("drupal/features", "https://www.drupal.org/files/issues/features-2808303-2.patch");
  }

  /**
   * @Update(
   *   version = "8.6.0-beta1",
   *   description = "Moves configuration files to blt subdirectory. Removes .git/hooks symlink."
   * )
   */
  public function update_860() {
    // Move files to blt subdir.
    $this->updater->moveFile('project.yml', 'blt/project.yml', TRUE);
    $this->updater->moveFile('project.local.yml', 'blt/project.local.yml', TRUE);
    $this->updater->moveFile('example.project.local.yml', 'blt/example.project.local.yml', TRUE);

    // Delete symlink to hooks directory. Individual git hooks are now symlinked, not the entire directory.
    $this->updater->deleteFile('.git/hooks');
    $this->updater->getOutput()->writeln('.git/hooks was deleted. Please re-run setup:git-hooks to install git hooks locally.');

    $this->updater->removeComposerRepository('https://github.com/mortenson/composer-patches');
    $this->updater->removeComposerScript('post-create-project-cmd');

    // Change 'deploy' module key to 'prod'.
    // @see https://github.com/acquia/blt/pull/700.
    $project_config = $this->updater->getProjectConfig();
    if (!empty($project_config['modules']['deploy'])) {
      $project_config['modules']['prod'] = $project_config['modules']['deploy'];
      unset($project_config['modules']['deploy']);
    }

    $this->updater->getOutput()->writeln("<comment>You MUST remove .travis.yml and re-initialize Travis CI support with `blt ci:travis:init`.</comment>");
  }

}
