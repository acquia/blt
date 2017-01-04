<?php

/**
 *
 *
 * Example usage:
 *
 *
 */
require_once 'phing/Task.php';

class YamlLintTask extends Task {

  protected $filesets = array(); // all fileset objects assigned to this task

  /**
   * Path the the yaml-cli executable.
   *
   * @var string
   */
  protected $executable = 'yaml-cli';

  /**
   * Stop processing on first failed scenario.
   *
   * @var bool
   */
  protected $haltonerror = false;

  /**
   * The return value.
   *
   * @var null
   */
  protected $return_property = null;

  /**
   * Nested adder, adds a set of files (nested fileset attribute).
   *
   * @param FileSet $fs
   * @return void
   */
  public function addFileSet(FileSet $fs)
  {
    $this->filesets[] = $fs;
  }

  /**
   * Set the path to the yaml-cli executable.
   *
   * @param string $str The executable
   *
   * @return void
   */
  public function setExecutable($str)
  {
    $this->executable = $str;
  }

  /**
   * Sets the flag if test execution should stop in the event of a failure.
   *
   * @param bool $stop If all tests should stop on failure.
   *
   * @return void
   */
  public function setHaltonerror($stop)
  {
    $this->haltonerror = StringHelper::booleanValue($stop);
  }

  /**
   * The Phing property the return code should be assigned to.
   *
   * @param string $str The Phing property.
   *
   * @return void
   */
  public function setReturnProperty($str)
  {
    $this->return_property = $str;
  }

  /**
   * The main entry point method.
   *
   * @throws BuildException
   * @return bool $return
   */
  public function main() {

    if (count($this->filesets) == 0) {
      throw new BuildException("You must define a fileset.");
    }

    if (!$this->yamlCliExists($this->executable)) {
      throw new BuildException(
        'ERROR: the yaml-cli executable "'.$this->executable.'" does not exist.',
        $this->getLocation()
      );
    }

    foreach ($this->getFilesetFiles() as $file) {
      $this->log("Linting $file", Project::MSG_VERBOSE);
      $command = "{$this->executable} lint $file";
      $last_line = system($command, $return);

      if ($return) {
        // If this is non-zero, there was a failure.
        $this->log("The file $file does not contain valid YAML.", Project::MSG_ERR);
        // Throw an exception if Behat fails.
        if ($this->haltonerror && $return != 0) {
          throw new BuildException("yaml-cli exited with code $return");
        }
      }
    }

    $this->log("All scanned YAML files are valid.");

    if (!empty($this->return_property)) {
      $this->getProject()
        ->setProperty($this->return_property, $return);
    }

    return $return != 0;
  }

  /**
   * Checks if the yaml-cli executable exists.
   *
   * @param string $executable The path to Behat
   *
   * @return bool
   */
  protected function yamlCliExists($executable)
  {
    // First check if the executable path is a file.
    if (is_file($executable)) {
      return true;
    }
    // Now check to see if the executable has a path.
    $return = shell_exec('type '.escapeshellarg($executable));

    return (empty($return) ? false : true);
  }


  /**
   * Return the list of files to parse
   *
   * @see PhpCodeSnifferTask
   *
   * @return string[] list of absolute files to parse
   */
  protected function getFilesetFiles()
  {
    $files = array();

    foreach ($this->filesets as $fs) {
      $dir = $fs->getDir($this->project)->getAbsolutePath();
      foreach ($fs->getDirectoryScanner($this->project)->getIncludedFiles() as $filename) {
        $file_path = $dir . DIRECTORY_SEPARATOR . $filename;
        $files[] = $file_path;
      }
    }

    return $files;
  }
}
