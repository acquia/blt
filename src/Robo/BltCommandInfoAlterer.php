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
   * @param $commandFileInstance
   */
  public function alterCommandInfo(CommandInfo $commandInfo, $commandFileInstance) {
  }

}
