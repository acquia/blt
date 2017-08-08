<?php

namespace Acquia\Blt\Custom;

// Do not remove this, even though it appears to be unused.
// @codingStandardsIgnoreLine
use Acquia\Blt\Annotations\Fileset;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Robo\Contract\ConfigAwareInterface;

/**
 * Class Filesets.
 *
 * Each fileset in this class should be tagged with a @fileset annotation and
 * should return \Symfony\Component\Finder\Finder object.
 *
 * @package Acquia\Blt\Custom
 * @see \Acquia\Blt\Robo\Filesets\Filesets
 */
class Filesets implements ConfigAwareInterface {
  use ConfigAwareTrait;

}
