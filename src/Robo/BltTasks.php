<?php

namespace Acquia\Blt\Robo;

use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\LocalEnvironment\LocalEnvironment;
use Acquia\Blt\Robo\LocalEnvironment\LocalEnvironmentInterface;
use Acquia\Blt\Robo\LocalEnvironment\LocalEnvironmentTrait;
use Robo\Tasks;

class BltTasks extends Tasks implements LocalEnvironmentInterface {

  use IO;
  use LocalEnvironmentTrait;

}
