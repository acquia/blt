<?php

/**
 * A phing task to loop through properties.
 */
require_once 'phing/Task.php';
use Symfony\Component\Yaml\Yaml;

class DisableTargetsTask extends PropertyTask {

  /**
   * Path to the yml file that defines $this->property.
   *
   * @var string
   */
  protected $file;

  /**
   * The name of the property listing the targets to be disabled.
   *
   * @var string
   */
  protected $property;

  /**
   * Set the file path.
   *
   * @param string $file
   *   A path to a valid yml file.
   */
  public function setFile($file) {
    $this->file = $file;
  }

  /**
   * Set the return property name.
   *
   * @param string $property
   */
  public function setProperty($property) {
    $this->property = $property;
  }


  /**
   * Getter for file property.
   *
   * @return string
   */
  public function getFile() {
    return $this->file;
  }

  /**
   * Getter for property property.
   *
   * @return string
   */
  public function getProperty() {
    return empty($this->property) ? 'disable-targets' : $this->property;
  }

  /**
   * The main entry point method.
   *
   * @return bool
   * @throws BuildException
   */
  public function main() {

    $file = $this->loadYamlFile($this->getFile());

    if (empty($file[$this->getProperty()])) {
      $this->log("Property {$this->getProperty()} does not exist in {$this->getFile()}.", PROJECT::MSG_DEBUG);

      return FALSE;
    }

    $targets_to_replace = $this->arrayFlatten($file[$this->getProperty()]);
    $project_targets = $this->getProject()->getTargets();
    foreach ($targets_to_replace as $target_name => $disable) {
      if ($disable) {
        if (empty($project_targets[$target_name])) {
          $this->log("Cannot disable target $target_name, it does not exist.", PROJECT::MSG_WARN);
        }
        else {
          $replacement_target = new Target();
          $echo_task = new EchoTask();
          $echo_task->setMessage("$target_name is disabled in {$this->getFile()}, skipping.");
          $echo_task->setLevel('warning');
          $echo_task->setProject($this->project);
          $replacement_target->addTask($echo_task);
          $replacement_target->setName($target_name);
          $this->project->addOrReplaceTarget($target_name, $replacement_target);
        }
      }
    }

    return TRUE;
  }

  /**
   * Load the Yaml file given.
   *
   * @param string $file
   *   A file path.
   *
   * @return array
   *   An associative array of parsed YAML.
   *
   * @throws BuildException
   */
  public function loadYamlFile($file) {
    try {
      if (file_exists($file)) {
        $data = Yaml::parse(file_get_contents($file));
      }
    } catch (Exception $e) {
      throw new BuildException('Could not load given file.');
    }

    return $data;
  }

  /**
   * Flatten array values into a string.
   *
   * @param  [type] $array [description]
   * @return [type]        [description]
   */
  public function arrayFlatten($array) {
    $result = array();
    $stack = array();
    array_push($stack, array("", $array));
    while (count($stack) > 0) {
      list($prefix, $array) = array_pop($stack);
      foreach ($array as $key => $value) {
        $new_key = $prefix . strval($key);
        if (is_array($value)) {
          array_push($stack, array($new_key . ':', $value));
        } else {
          $result[$new_key] = $value;
        }
      }
    }
    return $result;
  }

}
