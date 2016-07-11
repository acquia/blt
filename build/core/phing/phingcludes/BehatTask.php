<?php

/**
 * A Phing task to run Behat commands.
 */
require_once 'phing/Task.php';

/**
 * A Behat task. Runs behavior-driven development tests against a codebase.
 *
 * @author Adam Malone <adam@adammalone.net>
 */
class BehatTask extends Task
{
  protected $file;    // the source file (from xml attribute)
  protected $filesets = array(); // all fileset objects assigned to this task

  /**
   * Path the the Behat executable.
   *
   * @var string
   */
  protected $executable = 'behat';

  /**
   * Optional path(s) to execute.
   *
   * @var null
   */
  protected $path = null;

  /**
   * Specify config file to use.
   *
   * @var null
   */
  protected $config = null;

  /**
   * How to format tests output. pretty is default.
   *
   * @var null
   */
  protected $format = null;

  /**
   * Write format output to a file/directory instead of STDOUT (output_path).
   * @var null
   */
  protected $out = null;

  /**
   * Only executeCall the feature elements which match part
   * of the given name or regex.
   *
   * @var null
   */
  protected $name = null;

  /**
   * Only executeCall the features or scenarios with tags
   * matching tag filter expression.
   *
   * @var null
   */
  protected $tags = null;

  /**
   * Only executeCall the features with actor role matching
   * a wildcard.
   *
   * @var null
   */
  protected $role = null;

  /**
   * Specify config profile to use.
   *
   * @var null
   */
  protected $profile = null;

  /**
   * Only execute a specific suite.
   *
   * @var null
   */
  protected $suite = null;

  /**
   * Passes only if all tests are explicitly passing.
   *
   * @var bool
   */
  protected $strict = false;

  /**
   * Increase verbosity of exceptions.
   *
   * @var bool
   */
  protected $verbose = false;

  /**
   * Force ANSI color in the output.
   *
   * @var bool
   */
  protected $colors = true;

  /**
   * Invokes formatters without executing the tests and hooks.
   *
   * @var bool
   */
  protected $dryRun = false;

  /**
   * Stop processing on first failed scenario.
   *
   * @var bool
   */
  protected $haltonerror = false;

  /**
   * The output logs to be returned.
   *
   * @var null
   */
  protected $output_property = null;

  /**
   * The return value.
   *
   * @var null
   */
  protected $return_property = null;

  /**
   * All Behat options to be used to create the command.
   *
   * @var array
   */
  protected $options = array();

  /**
   * Set the path to the Behat executable.
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
   * Set the path to features to test.
   *
   * @param string $path The path to features.
   *
   * @return void
   */
  public function setPath($path)
  {
    $this->path = $path;
  }

  /**
   * Sets the Behat config file to use.
   *
   * @param string $config The config file
   *
   * @return void
   */
  public function setConfig($config)
  {
    $this->config = $config;
  }

  /**
   * Sets the name of tests to run.
   *
   * @param string $name The feature name to match
   *
   * @return void
   */
  public function setName($name)
  {
    $this->name = $name;
  }

  /**
   * Sets the test tags to use.
   *
   * @param string $tags The tag(s) to use
   *
   * @return void
   */
  public function setTags($tags)
  {
    $this->tags = $tags;
  }

  /**
   * Sets the output format.
   *
   * @param string $format The output format.
   *
   * @return void
   */
  public function setFormat($format) {
    $this->format = $format;
  }

  /**
   * Sets the output destination.
   *
   * @param string $out The output destination.
   *
   * @return void
   */
  public function setOut($out) {
    $this->out = $out;
  }

  /**
   * Sets the role able to run tests.
   *
   * @param string $role The actor role to match.
   *
   * @return void
   */
  public function setRole($role)
  {
    $this->role = $role;
  }

  /**
   * Set the profile to use for tests.
   *
   * @param string $profile The profile to use.
   *
   * @return void
   */
  public function setProfile($profile)
  {
    $this->profile = $profile;
  }

  /**
   * Set the test suite to use.
   *
   * @param string $suite The suite to use.
   *
   * @return void
   */
  public function setSuite($suite)
  {
    $this->suite = $suite;
  }

  /**
   * Sets the flag if strict testing should be enabled.
   *
   * @param bool $strict Behat strict mode.
   *
   * @return void
   */
  public function setStrict($strict)
  {
    $this->strict = StringHelper::booleanValue($strict);
  }

  /**
   * Sets the flag if a verbose output should be used.
   *
   * @param bool $verbose Use verbose output.
   *
   * @return void
   */
  public function setVerbose($verbose)
  {
    $this->verbose = StringHelper::booleanValue($verbose);
  }

  /**
   * Either force ANSI colors on or off.
   *
   * @param bool $colors Use ANSI colors.
   *
   * @return void
   */
  public function setColors($colors)
  {
    $this->colors = StringHelper::booleanValue($colors);
  }

  /**
   * Invokes test formatters without running tests against a site.
   *
   * @param bool $dryrun Run without testing.
   *
   * @return void
   */
  public function setDryRun($dryrun)
  {
    $this->dryRun = StringHelper::booleanValue($dryrun);
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
   * Rejigs the options array into a meaningful string of
   * command line arguments.
   *
   * @param mixed  $name  The option name
   * @param string $value The option value
   *
   * @return string
   */
  protected function createOption($name, $value)
  {
    if (is_array($value)) {
      $return = '';
      foreach ($value as $part) {
        $return .= "--$name=$part ";
      }
      return $return;
    }

    if (is_numeric($name)) {
      return '--'.$value;
    }

    return '--'.$name.'='.$value;
  }

  /**
   * Checks if the Behat executable exists.
   *
   * @param string $executable The path to Behat
   *
   * @return bool
   */
  protected function behatExists($executable)
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
   * The main entry point method.
   *
   * @throws BuildException
   * @return bool $return
   */
  public function main()
  {
    $command = array();

    if (!$this->behatExists($this->executable)) {
      throw new BuildException(
        'ERROR: the Behat executable "'.$this->executable.'" does not exist.',
        $this->getLocation()
      );
    }
    $command[] = $this->executable;

    if ($this->path) {
      if (!file_exists($this->path)) {
        throw new BuildException(
          'ERROR: the "'.$this->path.'" path does not exist.',
          $this->getLocation()
        );
      }
    }
    $command[] = !empty($this->path) ? $this->path : '';

    if ($this->config) {
      if (!file_exists($this->config)) {
        throw new BuildException(
          'ERROR: the "'.$this->config.'" config file does not exist.',
          $this->getLocation()
        );
      }

      $this->options['config'] = $this->config;
    }

    if ($this->name) {
      $this->options['name'] = $this->name;
    }

    if ($this->tags) {
      $this->options['tags'] = $this->tags;
    }

    if ($this->format) {
      $this->options['format'] = explode(',', $this->format);
    }

    if ($this->out) {
      $this->options['out'] = explode(',', $this->out);
    }

    if ($this->role) {
      $this->options['role'] = $this->role;
    }

    if ($this->profile) {
      $this->options['profile'] = $this->profile;
    }

    if ($this->suite) {
      $this->options['suite'] = $this->suite;
    }

    if ($this->strict) {
      $this->options[] = 'strict';
    }

    if ($this->verbose) {
      $this->options[] = 'verbose';
    }

    if ($this->colors) {
      $this->options[] = 'colors';
    }

    if ($this->dryRun) {
      $this->options[] = 'dry-run';
    }

    if ($this->haltonerror) {
      $this->options[] = 'stop-on-failure';
    }

    // Contract all options into the form Behat expects.
    foreach ($this->options as $name => $value) {
      $command[] = $this->createOption($name, $value);
    }
    $command = implode(' ', $command);
    $this->log("Running '$command'");

    // Run Behat.
    $last_line = system($command, $return);

    // Return the Behat exit value to a Phing property if specified.
    if (!empty($this->return_property)) {
      $this->getProject()
        ->setProperty($this->return_property, $return);
    }

    // Throw an exception if Behat fails.
    if ($this->haltonerror && $return != 0) {
      throw new BuildException("Behat exited with code $return");
    }

    return $return != 0;
  }
}
