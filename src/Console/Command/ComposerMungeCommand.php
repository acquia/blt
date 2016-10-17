<?php

namespace Acquia\Blt\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ComposerMungeCommand extends Command
{
  protected function configure()
  {
    $this
      ->setName('composer:munge')
      ->setDescription('Munge values in two composer.json files')
      ->addArgument(
        'file1',
        InputArgument::REQUIRED,
        'The first composer.json. Any conflicts will prioritize the value in this file.'
      )
      ->addArgument(
        'file2',
        InputArgument::REQUIRED,
        'The second composer.json.'
      )
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $file1 = $input->getArgument('file1');
    $file2 = $input->getArgument('file2');
    $munged_json = $this->munge($file1, $file2);

    $output->writeln($munged_json);
  }

  protected $repoRoot = '';

  /**
   * @param $file1
   * @param $file2
   */
  protected function munge($file1, $file2) {
    $default_contents = [
      'repositories' => [],
    ];
    $file1_contents = (array) json_decode(file_get_contents($file1), true) + $default_contents;
    $file2_contents = (array) json_decode(file_get_contents($file2), true) + $default_contents;

    $exclude_keys = [];
    if (!empty($file1_contents['extra']['blt']['composer-exclude-merge'])) {
      $exclude_keys = $file1_contents['extra']['blt']['composer-exclude-merge'];
    }
    // Skip merging entirely if '*' is excluded.
    if ($exclude_keys == '*') {
      $output = $file1_contents;
    }
    else {
      $output = $this->mergeKeyed($file1_contents, $file2_contents, $exclude_keys);

      if (empty($exclude_keys['repositories'])) {
        $output['repositories'] = $this->mergeRepositories((array) $file1_contents['repositories'], (array) $file2_contents['repositories']);
      }
    }

    $output_json = json_encode($output, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);

    return $output_json;
  }

  /**
   * Merges specific keyed arrays and objects in composer.json files.
   *
   * @param $file1_contents
   * @param $file2_contents
   * @param array $exclude_keys
   *
   * @return mixed
   */
  protected function mergeKeyed($file1_contents, $file2_contents, $exclude_keys = []) {
    // Merge keyed arrays objects.
    $merge_keys = [
      'autoload-dev',
      'extra',
      'require',
      'require-dev',
      'scripts',
    ];
    $output = $file1_contents;
    foreach ($merge_keys as $key) {

      // Handle exclusions.
      if (in_array($key, array_keys($exclude_keys))) {
        $excludes_value = $exclude_keys[$key];

        // Wildcard exclusion.
        if (is_string($excludes_value)) {
          // "require": "*"
          if ($excludes_value == '*') {
            continue;
          }
          // "require": "drupal/core"
          else {
            unset($file2_contents[$key][$excludes_value]);
          }
        }
        // "require": [ "drupal/core" ]
        elseif (is_array($excludes_value)) {
          foreach ($excludes_value as $exclude_package) {
            unset($file2_contents[$key][$exclude_package]);
          }
        }
      }

      // Set empty keys to empty placeholder arrays.
      if (!array_key_exists($key, $file1_contents)) {
        $file1_contents[$key] = [];
      }
      if (!array_key_exists($key, $file2_contents)) {
        $file2_contents[$key] = [];
      }

      // Merge!
      $output[$key] = $this->array_merge_recursive_distinct($file1_contents[$key], $file2_contents[$key]);
    }

    return $output;
  }

  /**
   * Merges the repositories array, which is unkeyed.
   *
   * @param $file1_contents
   * @param $file2_contents
   */
  protected function mergeRepositories($file1_repos, $file2_repos) {
    $repos = array_merge($file1_repos, $file2_repos);
    $repos = array_unique($repos, SORT_REGULAR);

    return $repos;
  }

  /**
   * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
   * keys to arrays rather than overwriting the value in the first array with the duplicate
   * value in the second array, as array_merge does. I.e., with array_merge_recursive,
   * this happens (documented behavior):
   *
   * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
   *     => array('key' => array('org value', 'new value'));
   *
   * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
   * Matching keys' values in the second array overwrite those in the first array, as is the
   * case with array_merge, i.e.:
   *
   * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
   *     => array('key' => array('new value'));
   *
   * Parameters are passed by reference, though only for performance reasons. They're not
   * altered by this function.
   *
   * Additionally, array_merge_recursive_distinct will not overwrite numerically keyed rows.
   * Instead it will append them to the parent array.
   *
   * @param array $array1
   * @param array $array2
   * @return array
   * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
   * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
   * @author Matthew Grasmick <matt (dot) grasmick (at) gmail (dot) com>
   * @see http://php.net/manual/en/function.array-merge-recursive.php#92195
   */
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
