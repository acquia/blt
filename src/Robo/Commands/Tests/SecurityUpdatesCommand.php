<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\BltTasks;
use Composer\Semver\Comparator;
use GuzzleHttp\Client;
use Symfony\Component\Console\Helper\Table;

/**
 * Defines commands in the "tests" namespace.
 */
class SecurityUpdatesCommand extends BltTasks {

  /**
   * Check local Drupal installation for security updates.
   *
   * @command tests:security-updates
   * @description Check local Drupal installation for security updates.
   *
   * @interactInstallDrupal
   * @validateDrupalIsInstalled
   */
  public function testsSecurityUpdates() {
    $security_updates = [];
    $client = new Client();
    $data = json_decode($client->get('https://raw.githubusercontent.com/drupal-composer/drupal-security-advisories/8.x/composer.json')->getBody(), TRUE);
    $composer_lock = json_decode(file_get_contents($this->getConfigValue('repo.root') . '/composer.lock'), TRUE);
    foreach ($composer_lock['packages'] as $key => $package) {
      $name = $package['name'];
      if (!empty($data['conflict'][$name])) {
        $conflict_constraints = explode(',', $data['conflict'][$name]);
        foreach ($conflict_constraints as $conflict_constraint) {
          if (substr($conflict_constraint, 0, 1) == '<') {
            $min_version = substr($conflict_constraint, 1);
            if (Comparator::lessThan($package['version'], $min_version)) {
              $security_updates[$name] = [
                $name,
                $package['version'],
                $min_version,
              ];
            }
          }
          else {
            // Unparsable constraint.
          }
        }
      }
    }
    if ($security_updates) {
      $this->logger->error("One or more of your dependencies has an outstanding security update. Please apply update(s) immediately.");
      $table = new Table($this->output);
      $table->setHeaders(['Name', 'Installed version', 'Suggested version'])
        ->setRows($security_updates)
        ->render();
      $this->logger->notice('To disable security checks, set disable-targets.tests.security-updates to false in project.yml.');
      return 1;
    }
    else {
      $this->writeln("<info>There are no outstanding security updates for Drupal projects.</info>");
      return 0;
    }
  }

}
