<?php

namespace Acquia\Blt\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 */
class TextMungeCommand extends BaseCommand {

  /**
   * ${inheritdoc}.
   */
  protected function configure() {
    $this
      ->setName('text:munge')
      ->setAliases(['txt:munge'])
      ->setDescription('Munge values in two text|txt files')
      ->addArgument(
        'file1',
        InputArgument::REQUIRED,
        'The first text or txt.'
      )
      ->addArgument(
        'file2',
        InputArgument::REQUIRED,
        'The second text or txt.'
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

  /**
   * Merges the arrays in two text files.
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
    $file1_contents = file($file1);
    $file2_contents = file($file2);

    $munged_contents = self::arrayMergeNoDuplicates($file1_contents, $file2_contents);

    return $munged_contents;
  }

  /**
   * Merges two arrays and removes duplicate values.
   *
   * @param array $array1
   *   The first array.
   * @param array $array2
   *   The second array.
   *
   * @return array
   */
  public static function arrayMergeNoDuplicates(array &$array1, array &$array2) {
    $merged = array_merge($array1, $array2);
    $merged_without_dups = array_unique($merged);

    return $merged_without_dups;
  }

}
