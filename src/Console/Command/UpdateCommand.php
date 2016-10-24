<?php

namespace Acquia\Blt\Console\Command;

use Acquia\Blt\Update\Updater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class UpdateCommand extends Command
{
  protected function configure()
  {
    $this
      ->setName('blt:update')
      ->setDescription('Performs BLT updates for specific version delta.')
      ->addArgument(
        'starting_version',
        InputArgument::REQUIRED,
        'The starting version'
      )
      ->addArgument(
        'ending_version',
        InputArgument::REQUIRED,
        'The ending version.'
      )
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $starting_version = $input->getArgument('starting_version');
    $ending_version = $input->getArgument('ending_version');

    $updater = new Updater();
    $updates = $updater->getUpdates($starting_version, $ending_version);
    if ($updates) {
      $output->writeln("<comment>The following BLT updates are outstanding:");
      $updater->printUpdates($updates);
      $question = new ConfirmationQuestion(
        '<question>Would you like to perform the listed updates?</question> ',
        false
      );
      $continue = $this->getHelper('question')->ask($input, $output, $question);
      if (!$continue) {
        return 1;
      }

      $updater->executeUpdates($updates);
    }

  }
}
