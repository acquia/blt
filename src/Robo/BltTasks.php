<?php

namespace Acquia\Blt\Robo;

use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Acquia\Blt\Robo\LocalEnvironment\LocalEnvironmentInterface;
use Acquia\Blt\Robo\LocalEnvironment\LocalEnvironmentTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Contract\ConfigAwareInterface;
use Robo\Tasks;

class BltTasks extends Tasks implements ConfigAwareInterface, LocalEnvironmentInterface, LoggerAwareInterface {

  use ConfigAwareTrait;
  use IO;
  use LocalEnvironmentTrait;
  use LoggerAwareTrait;
}
