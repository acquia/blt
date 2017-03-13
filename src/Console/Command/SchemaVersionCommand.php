<?php

namespace Acquia\Blt\Console\Command;

use Acquia\Blt\Update\Updater;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 *
 */
class SchemaVersionCommand extends BaseCommand {

  /**
   * Defines configuration for `blt:schema-version`.
   */
  protected function configure() {
    $this
      ->setName('blt:schema-version')
      ->setDescription('Determines BLT schema version.')
      ->addArgument(
        'repo_root',
        InputArgument::REQUIRED,
        'The root directory of the repository that utilizes BLT.'
      )
      ->addOption(
        'latest',
        'l',
        InputOption::VALUE_NONE,
        'Get the latest available schema version number, rather than the current one.'
      );
  }

  /**
   * Executes `blt:schema-version`.
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $repo_root = $input->getArgument('repo_root');
    if ($input->getOption('latest')) {
      $updater = new Updater('Acquia\Blt\Update\Updates', $repo_root);
      $output->writeln($updater->getLatestUpdateMethodVersion());
    }
  }

}
