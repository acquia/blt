<?php

namespace Acquia\Blt\Composer;

use Composer\Script\Event;

/**
 * Class Hooks
 *
 * @package Acquia\Blt\Autoconfig
 *
 * Works around upstream issue with Composer dependency sorting.
 * @see https://github.com/composer/composer/issues/8065
 */
class Hooks {

  /**
   * Generate the _autoconfig_ file on 'autoload_dump' Composer event.
   *
   * @param Event $event
   */
  public static function postAutoloadDump(Event $event) {
    $composer = $event->getComposer();
    $vendor_dir = $composer->getConfig()->get('vendor-dir');
    $installed_json = realpath($vendor_dir) . "/composer/installed.json";
    $installed = json_decode(file_get_contents($installed_json));
    foreach ($installed as $key => $package) {
      if ($package->name == 'composer/installers') {
        unset($installed[$key]);
        array_unshift($installed, $package);
      }
    }
    file_put_contents($installed_json, json_encode($installed, 448));
  }
}
