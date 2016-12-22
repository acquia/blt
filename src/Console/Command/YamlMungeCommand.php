<?php

namespace Acquia\Blt\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class YamlMungeCommand extends Command
{
  protected function configure()
  {
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
      )
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $file1 = $input->getArgument('file1');
    $file2 = $input->getArgument('file2');
    $munged_contents = $this->munge($file1, $file2);
    $output->writeln($munged_contents);
  }

  protected $repoRoot = '';

  /**
   * @param $file1
   * @param $file2
   */
  protected function munge($file1, $file2) {
    $file1_contents = $this->parseFile($file1);
    $file2_contents = $this->parseFile($file2);

    $munged_contents = array_replace_recursive((array) $file1_contents, (array) $file2_contents);

    return Yaml::dump($munged_contents, 3, 2);
  }

  protected function parseFile($file) {
    try {
      $value = Yaml::parse(file_get_contents($file));
    } catch (ParseException $e) {
      printf("Unable to parse the YAML string: %s", $e->getMessage());
    }

    return $value;
  }
}
