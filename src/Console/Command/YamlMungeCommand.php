<?php

namespace Acquia\Blt\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 *
 */
class YamlMungeCommand extends BaseCommand {

  /**
   * ${inheritdoc}.
   */
  protected function configure() {
    $this
      ->setName('yaml:munge')
      ->setAliases(['yml:munge'])
      ->setDescription('Munge values in two yaml|yml files')
      ->addArgument(
        'file1',
        InputArgument::REQUIRED,
        'The first yaml or yml. Any conflicts will prioritize the value in this file.'
      )
      ->addArgument(
        'file2',
        InputArgument::REQUIRED,
        'The second yaml or yml.'
      );
  }

  /**
   * ${inheritdoc}.
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $file1 = $input->getArgument('file1');
    $file2 = $input->getArgument('file2');
    $munged_contents = $this->munge($file1, $file2);
    $output->writeln($munged_contents);
  }

  protected $repoRoot = '';

  /**
   * Merges the arrays in two yaml files.
   *
   * @param string $file1
   *   The file path of the first file.
   * @param string $file2
   *   The file path of the second file.
   *
   * @return string
   *   The merged arrays, in yaml format.
   */
  protected function munge($file1, $file2) {
    $file1_contents = (array) $this->parseFile($file1);
    $file2_contents = (array) $this->parseFile($file2);

    $munged_contents = self::arrayMergeRecursiveExceptEmpty($file1_contents, $file2_contents);

    return Yaml::dump($munged_contents, 3, 2);
  }

  /**
   * Parses a yaml file.
   *
   * @param string $file
   *   The file path.
   *
   * @return array
   *   The parsed yaml file.
   *
   * @throws \Symfony\Component\Yaml\Exception\ParseException
   */
  protected function parseFile($file) {
    try {
      $value = Yaml::parse(file_get_contents($file));
    }
    catch (ParseException $e) {
      printf("Unable to parse the YAML string: %s", $e->getMessage());
    }

    return $value;
  }

  /**
   * Recursively merges arrays UNLESS second array is empty.
   *
   * Preserves data types. If value in second array is empty, it will REPLACE
   * the corresponding key in the first array, rather than being merged.
   *
   * @param array $array1
   *   The first array.
   * @param array $array2
   *   The second array.
   *
   * @return array
   */
  public static function arrayMergeRecursiveExceptEmpty(array &$array1, array &$array2) {
    $merged = $array1;

    foreach ($array2 as $key => &$value) {
      if (is_array($value) && isset($merged[$key]) && is_array($merged[$key]) && !empty($value)) {
        $merged[$key] = self::arrayMergeRecursiveExceptEmpty($merged[$key], $value);
      }
      else {
        $merged[$key] = $value;
      }
    }

    return $merged;
  }

}
