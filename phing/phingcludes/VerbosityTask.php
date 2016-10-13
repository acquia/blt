<?php

/**
 * A phing task to loop through properties.
 */
require_once 'phing/Task.php';
use Symfony\Component\Yaml\Yaml;

class VerbosityTask extends Task {

  /**
   * Path the Drush executable.
   */
  public function setLevel($str) {
    $this->level = $str;
  }


  /**
   * The main entry point method.
   *
   * @return bool
   * @throws BuildException
   */
  public function main() {

    $map = [
      'debug' => Project::MSG_DEBUG,
      'verbose' =>  Project::MSG_VERBOSE,
      'info' =>  Project::MSG_INFO,
      'warn' =>  Project::MSG_WARN,
      'error' =>  Project::MSG_ERR,
    ];

    if (is_numeric($this->level)) {
      $this->setLoggerLevel($this->level);
    }
    elseif (array_key_exists($this->level, $map)) {
      $this->setLoggerLevel($map[$this->level]);
    }
    else {
      $this->log("blt.level '{$this->level}' is invalid. Acceptable values are " . implode(', ', array_keys($map)), Project::MSG_ERR);
    }
  }
  public function setLoggerLevel($level) {
    $listeners = $this->getProject()->getBuildListeners();
    foreach ($listeners as $listener) {
      $listener->setMessageOutputLevel($level);
    }
  }

}
