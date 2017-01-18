<?php

namespace Acquia\Blt\Robo;

use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Acquia\Blt\Robo\LocalEnvironment\LocalEnvironmentInterface;
use Acquia\Blt\Robo\LocalEnvironment\LocalEnvironmentTrait;
use Robo\Contract\ConfigAwareInterface;
use Robo\Tasks;

class BltTasks extends Tasks implements LocalEnvironmentInterface, ConfigAwareInterface {

  use IO;
  use LocalEnvironmentTrait;
  use ConfigAwareTrait;
}
