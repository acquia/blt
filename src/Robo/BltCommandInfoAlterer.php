<?php

namespace Acquia\Blt\Robo;

use Consolidation\AnnotatedCommand\CommandInfoAltererInterface;
use Consolidation\AnnotatedCommand\Parser\CommandInfo;

/**
 * Class BltCommandInfoAlterer.
 */
class BltCommandInfoAlterer implements CommandInfoAltererInterface {

  /**
   * Alters annotated commands.
   *
   * @param \Consolidation\AnnotatedCommand\Parser\CommandInfo $commandInfo
   *   Command info.
   * @param mixed $commandFileInstance
   *   Command file instance.
   */
  public function alterCommandInfo(CommandInfo $commandInfo, $commandFileInstance) {
  }

}
