<?php

namespace Acquia\Blt\Robo;

use Robo\Application;
use Symfony\Component\Console\Input\InputOption;

class BltApplication extends Application {

  /**
   * @param string $name
   * @param string $version
   */
  public function __construct($name, $version)
  {
    parent::__construct($name, $version);

//    $this->getDefinition()
//      ->addOption(
//        new InputOption('--simulate', null, InputOption::VALUE_NONE, 'Run in simulated mode (show what would have happened).')
//      );
  }

}
