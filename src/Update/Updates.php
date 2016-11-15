<?php

namespace Acquia\Blt\Update;

use Acquia\Blt\Annotations\Update;

class Updates {

  /** @var Updater */
  protected $updater;

  /**
   * @param Updater $updater
   */
  public function setUpdater($updater) {
    $this->updater = $updater;
  }

  /**
   * @Update(
   *   version = "8.5.1",
   *   description = "Removes deprecated features patch."
   * )
   */
  public function update_850() {
    $this->updater->removePatch("drupal/features", "https://www.drupal.org/files/issues/features-2808303-2.patch");
  }
}
