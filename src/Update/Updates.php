<?php

namespace Acquia\Blt\Update;

use Acquia\Blt\Annotations\Update;

class Updates {

  /** @var Updater */
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
    $this->updater->removePatch("drupal/features", "https://www.drupal.org/files/issues/features-2808303-2.patch");

    // Move files to blt subdir.
    $this->updater->moveFile('project.yml', 'blt/project.yml');
    $this->updater->moveFile('project.local.yml', 'blt/project.local.yml');
    $this->updater->moveFile('example.project.local.yml', 'blt/example.project.local.yml');
  }
}
