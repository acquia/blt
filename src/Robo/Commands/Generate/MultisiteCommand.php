<?php

namespace Acquia\Blt\Robo\Commands\Generate;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "generate:multisite" namespace.
 */
class MultisiteCommand extends BltTasks {

  /**
   * Generates a new multisite.
   *
   * @command generate:multisite
   *
   */
  public function generate() {
    $this->say("This will generate a new site in the docroot/sites directory.");
    $site_name = $this->ask("Machine name");
    $domain = $this->ask("Local domain name");
    $domain = $this->ask("Would you like to configure the local database credentials?");
    $domain = $this->ask("Local database name");
    $domain = $this->ask("Local database user");
    $domain = $this->ask("Local database password");
    $domain = $this->ask("Local database port");
    $domain = $this->ask("Would you like to generate a new virtual host entry for this site inside Drupal VM?");
    $domain = $this->ask("");
    $domain = $this->ask("");
    $domain = $this->ask("");
    $domain = $this->ask("");
    // @todo Prompts.
    // @todo Config split.
    // @todo Domain.
    // @todo DVM
  }

}
