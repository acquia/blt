<?php

namespace Acquia\Blt\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 */
class ComposerMungeCommand extends BaseCommand {

  /**
   * ${inheritdoc}.
   */
  protected function configure() {
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
      );
  }

  /**
   * ${inheritdoc}.
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $file1 = $input->getArgument('file1');
    $file2 = $input->getArgument('file2');

    if (!file_exists($file1)) {
      throw new \Exception("The file $file1 does not exist");
    }
    if (!file_exists($file2)) {
      throw new \Exception("The file $file2 does not exist");
    }

    $munged_json = $this->munge($file1, $file2);

    $output->writeln($munged_json);
  }

  /**
   * Selectively merges parts of two composer.json files.
   *
   * @param string $file1
   *   The file path to the first composer.json file.
   * @param string $file2
   *   The file path to the second composer.json file.
   *
   * @return string
   *   The new, merged composer.json contents.
   */
  protected function munge($file1, $file2) {
    $default_contents = [];
    $file1_contents = (array) json_decode(file_get_contents($file1), TRUE) + $default_contents;
    $file2_contents = (array) json_decode(file_get_contents($file2), TRUE) + $default_contents;

    $output = $this->mergeKeyed($file1_contents, $file2_contents);

    // Ensure that require and require-dev are objects and not arrays.
    if (array_key_exists('require', $output) && is_array($output['require'])) {
      $output['require'] = (object) $output['require'];
    }
    if (array_key_exists('require-dev', $output)&& is_array($output['require-dev'])) {
      $output['require-dev'] = (object) $output['require-dev'];
    }

    $output_json = json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

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
      'extra',
    ];
    $output = $file1_contents;
    foreach ($merge_keys as $key) {
      // Set empty keys to empty placeholder arrays.
      if (!array_key_exists($key, $file1_contents)) {
        $file1_contents[$key] = [];
      }
      if (!array_key_exists($key, $file2_contents)) {
        $file2_contents[$key] = [];
      }

      // Merge!
      $output[$key] = $this->arrayMergeRecursiveDistinct($file1_contents[$key], $file2_contents[$key]);
    }

    return $output;
  }

}
