<?php

/**
 * A phing task to loop through properties.
 */
require_once 'phing/Task.php';

class VerbosityTask extends Task {

  protected $return_property = null;

  /**
   * The name of a Phing property to assign the Drush command's output to.
   */
  public function setReturnProperty($str) {
    $this->return_property = $str;
  }

  /**
   * The main entry point method.
   *
   * @return bool
   * @throws BuildException
   */
  public function main() {

    // Set value of the return property.
    if (empty($this->return_property)) {
      throw new \Exception('You must set a return property for the Verbosity task.');
    }

    $map = [
      'debug' => Project::MSG_DEBUG,
      'verbose' =>  Project::MSG_VERBOSE,
      'info' =>  Project::MSG_INFO,
      'warn' =>  Project::MSG_WARN,
      'error' =>  Project::MSG_ERR,
    ];

    // If -verbose flag is used, ignore the rest.
    global $argv;
    foreach ($argv as $argument) {
      switch ($argv) {
        case '-debug':
        case '-verbose':
        case '-quiet':
        case '-silent':
          $level = substr($argument, 1, strlen($argument));
          $this->getProject()->setProperty($this->return_property, $level);
          return TRUE;

        break;
      }
    }

    // Set default level to info.
    $this->getProject()->setProperty($this->return_property, 'info');
  }

}
