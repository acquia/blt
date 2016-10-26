<?php

/**
 * @file
 * A Phing task to run Drush commands.
 */
require_once "phing/Task.php";

class DrushParam {

  private $value;

  public function addText($str) {
    $this->value = $str;
  }

  public function getValue() {
    return $this->value;
  }

}

class DrushOption {

  private $name;
  private $value;

  public function setName($str) {
    $this->name = $str;
  }

  public function getName() {
    return $this->name;
  }

  public function addText($str) {
    $this->value = $str;
  }

  public function getValue() {
    return $this->value;
  }

  public function toString() {
    $name  = $this->getName();
    $value = $this->getValue();
    $str = '--'.$name;
    if (!empty($value)) {
      $str .= '='.$value;
    }
    return $str;
  }

}

class DrushTask extends Task {

  /**
   * The message passed in the buildfile.
   */
  private $command = array();
  private $bin = NULL;
  private $alias = NULL;
  private $uri = NULL;
  private $dir = NULL;
  private $root = NULL;
  private $assume = NULL;
  private $simulate = FALSE;
  private $pipe = FALSE;
  private $options = array();
  private $params = array();
  private $return_property = NULL;
  private $verbose = FALSE;
  private $haltonerror = TRUE;
  private $passthru = FALSE;
  private $logoutput = TRUE;

  /**
   * The Drush command to run.
   */
  public function setCommand($str) {
    $this->command = $str;
  }

  /**
   * Path the Drush executable.
   */
  public function setBin($str) {
    $this->bin = $str;
  }

  /**
   * Drush alias to use.
   */
  public function setAlias($str) {
    $this->alias = $str;
  }

  /**
   * Drupal working directory to use.
   */
  public function setDir($str) {
    $this->dir = $str;
  }
  /**
   * Drupal root directory to use.
   */
  public function setRoot($str) {
    $this->root = $str;
  }

  /**
   * URI of the Drupal to use.
   */
  public function setUri($str) {
    $this->uri = $str;
  }

  /**
   * Assume 'yes' or 'no' to all prompts.
   */
  public function setAssume($var) {
    if ($var === "") {
      unset($this->assume);
    }
    elseif (is_string($var)) {
      $this->assume = ($var === 'yes' || $var === 'true');
    } else {
      $this->assume = !!$var;
    }
  }

  /**
   * Simulate all relevant actions.
   */
  public function setSimulate($var) {
    if (is_string($var)) {
      $var = strtolower($var);
      $this->simulate = ($var === 'yes' || $var === 'true');
    } else {
      $this->simulate = !!$var;
    }
  }

  /**
   * Use the pipe option.
   */
  public function setPipe($var) {
    if (is_string($var)) {
      $var = strtolower($var);
      $this->pipe = ($var === 'yes' || $var === 'true');
    } else {
      $this->pipe = !!$var;
    }
  }

  /**
   * The name of a Phing property to assign the Drush command's output to.
   */
  public function setReturnProperty($str) {
    $this->return_property = $str;
  }

  /**
   * The name of a Phing property to assign the Drush command's output to.
   */
  public function setHaltonerror($var) {
    if (is_string($var)) {
      $var = strtolower($var);
      $this->haltonerror = ($var === 'yes' || $var === 'true');
    } else {
      $this->haltonerror = !!$var;
    }
  }

  /**
   * Parameters for the Drush command.
   */
  public function createParam() {
    $o = new DrushParam();
    $this->params[] = $o;
    return $o;
  }

  /**
   * Options for the Drush command.
   */
  public function createOption() {
    $o = new DrushOption();
    $this->options[] = $o;
    return $o;
  }

  /**
   * Display extra information about the command.
   */
  public function setVerbose($var) {
    if (is_string($var)) {
      $this->verbose = ($var === 'yes' || $var === 'true');
    } else {
      $this->verbose = !!$var;
    }
  }

  /**
   * Use passthru() rather than exec() for command execution.
   */
  public function setPassthru($var) {
    if (is_string($var)) {
      $this->passthru = ($var === 'yes' || $var === 'true');
    } else {
      $this->passthru = !!$var;
    }
  }

  /**
   * Log output.
   */
  public function setLogOutput($var) {
    if (is_string($var)) {
      $this->logoutput = ($var === 'yes' || $var === 'true');
    } else {
      $this->logoutput = !!$var;
    }
  }

  /**
   * Initialize the task.
   */
  public function init() {
    // Get default root, uri and binary from project.
    $this->root = $this->getProject()->getProperty('drush.root');
    $this->uri = $this->getProject()->getProperty('drush.uri');
    $this->bin = $this->getProject()->getProperty('drush.bin');
    $this->dir = $this->getProject()->getProperty('drush.dir');
    $this->alias = $this->getProject()->getProperty('drush.alias');
    $this->setVerbose($this->getProject()->getProperty('drush.verbose'));
    $this->setAssume($this->getProject()->getProperty('drush.assume'));
    $this->setPassthru($this->getProject()->getProperty('drush.passthru'));
    $this->setLogOutput($this->getProject()->getProperty('drush.logoutput'));
  }

  /**
   * The main entry point method.
   */
  public function main() {
    $command = array();

    $command[] = !empty($this->bin) ? $this->bin : 'drush';

    if (!empty($this->alias)) {
      $command[] = '@' . $this->alias;
    }

    if (!empty($this->root)) {
      $option = new DrushOption();
      $option->setName('root');
      $option->addText($this->root);
      $this->options[] = $option;
    }

    if (!empty($this->uri)) {
      $option = new DrushOption();
      $option->setName('uri');
      $option->addText($this->uri);
      $this->options[] = $option;
    }

    if (is_bool($this->assume)) {
      $option = new DrushOption();
      $option->setName(($this->assume ? 'yes' : 'no'));
      $this->options[] = $option;
    }

    if ($this->simulate) {
      $option = new DrushOption();
      $option->setName('simulate');
      $this->options[] = $option;
    }

    if ($this->pipe) {
      $option = new DrushOption();
      $option->setName('pipe');
      $this->options[] = $option;
    }

    if (Phing::getMsgOutputLevel() >= Project::MSG_VERBOSE) {
      $this->setVerbose('true');
    }

    if ($this->verbose) {
      $option = new DrushOption();
      $option->setName('verbose');
      $this->options[] = $option;
      $exec_level = Project::MSG_INFO;
    }
    else {
      $exec_level = Project::MSG_VERBOSE;
    }

    foreach ($this->options as $option) {
      $command[] = $option->toString();
    }

    $command[] = $this->command;

    foreach ($this->params as $param) {
      $command[] = $param->getValue();
    }

    if (!empty($this->dir)) {
      $this->log("Changing working directory to: $this->dir", $exec_level);
      $initial_cwd = getcwd();
      chdir($this->dir);
    }

    // Execute Drush.
    $output = array();
    $return = NULL;

    if ($this->passthru) {
      $command = implode(' ', $command);
      $this->log("Executing: $command", $exec_level);
      passthru($command, $return);
    }
    else {
      // Redirect sterr to stout for Phing log.
      $command[] = '2>&1';

      $command = implode(' ', $command);
      $this->log("Executing: $command", $exec_level);
      exec($command, $output, $return);

      if ($this->logoutput) {
        // Collect Drush output for display through Phing's log.
        foreach ($output as $line) {
          $this->log($line);
        }
      }
    }

    if (isset($initial_cwd)) {
      $this->log("Changing working directory back to $initial_cwd.", $exec_level);
      chdir($initial_cwd);
    }

    // Set value of the return property.
    if (!empty($this->return_property)) {
      $this->getProject()->setProperty($this->return_property, $return);
    }
    // Build fail.
    if ($this->haltonerror && $return != 0) {
      throw new BuildException("Drush exited with code $return");
    }
    return $return != 0;
  }

}
