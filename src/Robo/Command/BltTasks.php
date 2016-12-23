<?php

namespace Acquia\Blt\Robo\Command;
use Robo\Tasks;
use Symfony\Component\Yaml\Yaml;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class BltTasks extends Tasks
{

  protected $config = [];
  protected $repoRoot;
  protected $bltRoot;
  protected $docroot;
  protected $bin;

  public function getName() {
    return 'blt';
  }

  /**
   * RoboFile constructor.
   */
  public function __construct() {
    $this->setRepoRoot();
    $this->setBltRoot();
    $this->docroot = "{$this->repoRoot}/docroot";
    $this->setConfig();
    $this->bin = "{$this->repoRoot}/vendor/bin";
  }

  /**
   *
   */
  public function setRepoRoot() {
    $possible_repo_roots = [
      $_SERVER['PWD'],
      getcwd(),
    ];

    foreach ($possible_repo_roots as $possible_repo_root) {
      if (file_exists("$possible_repo_root/blt/project.yml")) {
        $this->repoRoot = $possible_repo_root;
        break;
      }
    }
  }

  /**
   *
   */
  public function setBltRoot() {
    $this->bltRoot = dirname(dirname(dirname(dirname(__FILE__))));
  }

  /**
   *
   */
  public function setConfig() {
    $default_config =  Yaml::parse(file_get_contents("{$this->bltRoot}/phing/build.yml"));
    $this->config = Yaml::parse(file_get_contents("{$this->repoRoot}/blt/project.yml"));
    $this->config = $this->array_merge_recursive_distinct($this->config, $default_config);

    array_walk_recursive($this->config, function (&$value, $key) {
      $value = str_replace('${repo.root}', $this->repoRoot, $value);
      $value = str_replace('${blt.root}', $this->bltRoot, $value);
      $value = str_replace('${docroot}', $this->docroot, $value);
    });
  }

  protected function array_merge_recursive_distinct ( array &$array1, array &$array2 )
  {
    $merged = $array1;

    foreach ( $array2 as $key => &$value )
    {
      if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
      {
        $merged [$key] = $this->array_merge_recursive_distinct ( $merged [$key], $value );
      }
      else
      {
        $merged [$key] = $value;
      }
    }

    return $merged;
  }
}
