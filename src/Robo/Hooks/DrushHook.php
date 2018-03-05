<?php

namespace Acquia\Blt\Robo\Hooks;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Consolidation\AnnotatedCommand\CommandData;

/**
 * Validate Drush configuration for failed commands.
 */
class DrushHook extends BltTasks {

  /**
   * Validates drush configuration for failed commands.
   *
   * @hook validate @validateDrushConfig
   */
  public function validateDrushConfig(CommandData $commandData) {
    $alias = $this->getConfigValue('drush.alias');
    if ($alias && !$this->getInspector()->isDrushAliasValid("$alias")) {
      $this->logger->error("Invalid drush alias '@$alias'.");
      $this->logger->info('Troubleshooting suggestions:');
      $this->logger->info('Execute `drush site:alias` from within the docroot to see a list of available aliases.');
      $this->logger->info("Execute `drush site:alias $alias` for information on the @$alias alias.");
      $this->logger->info("Execute `drush @$alias status` to determine the status of the application belonging to the alias.");
      throw new BltException("Invalid drush alias '@$alias'.");
    }
  }

}
