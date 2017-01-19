<?php

namespace Acquia\Blt\Robo\Wizards;

use Acquia\Blt\Robo\Common\ExecutorAwareInterface;
use Acquia\Blt\Robo\Common\ExecutorAwareTrait;
use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Acquia\Blt\Robo\Inspector\InspectorAwareInterface;
use Acquia\Blt\Robo\Inspector\InspectorAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\IOAwareInterface;

/**
 * Class Wizard
 * @package Acquia\Blt\Robo\Wizards
 */
abstract class Wizard implements ConfigAwareInterface, ExecutorAwareInterface, InspectorAwareInterface, IOAwareInterface, LoggerAwareInterface {
  use ConfigAwareTrait;
  use ExecutorAwareTrait;
  use InspectorAwareTrait;
  use IO;
  use LoggerAwareTrait;
}
