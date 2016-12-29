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
class UpdateCommand extends BaseCommand {

  /**
   *
   */
  protected function configure() {
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
      ->addArgument(
        'repo_root',
        InputArgument::REQUIRED,
        'The root directory of the repository that utilizes BLT.'
      )
      ->addOption(
        'yes',
        'y',
        InputOption::VALUE_NONE,
        'Answers yes to all question prompts'
      );
  }

  /**
   *
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $starting_version = $input->getArgument('starting_version');
    $ending_version = $input->getArgument('ending_version');
    $repo_root = $input->getArgument('repo_root');

    $updater = new Updater('Acquia\Blt\Update\Updates', $repo_root);
    $updates = $updater->getUpdates($starting_version, $ending_version);
    if ($updates) {
      $output->writeln("<comment>The following BLT updates are outstanding:</comment>");
      $updater->printUpdates($updates);

      if (!$input->getOption('yes')) {
        $question = new ConfirmationQuestion(
          '<question>Would you like to perform the listed updates?</question> ',
          FALSE
        );

        $continue = $this->getHelper('question')->ask($input, $output, $question);
        if (!$continue) {
          return 1;
        }
      }

      $updater->executeUpdates($updates);
    }
    else {
      $output->writeln("<comment>There are no scripted updates available between BLT versions $starting_version and $ending_version.</comment>");
    }
  }

}
