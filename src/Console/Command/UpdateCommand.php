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
   * Defines configuration for `blt:update` command.
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
   * Executes `blt:update` command.
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $starting_version = $input->getArgument('starting_version');
    $repo_root = $input->getArgument('repo_root');

    $starting_version = $this->convertLegacySchemaVersion($starting_version);
    $updater = new Updater('Acquia\Blt\Update\Updates', $repo_root);
    $updates = $updater->getUpdates($starting_version);
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
      $latest_version = $updater->getLatestUpdateMethodVersion();
      $output->writeln("<comment>There are no scripted updates available between BLT versions $starting_version and $latest_version.</comment>");
    }
  }

  protected function convertLegacySchemaVersion($version) {
    // Check to see if version is Semver (legacy format). Convert to expected
    // syntax. Luckily, there are a finite number of known legacy versions.
    // We check specifically for those.
    // E.g., 8.6.6 => 8006006
    if (strpos($version, '.') !== FALSE) {
      str_replace('-beta1', '', $version);
      $semver_array = explode('.', $version);
      $semver_array[1] = str_pad($semver_array[1], 3, "0", STR_PAD_LEFT);
      $semver_array[2] = str_pad($semver_array[2], 3, "0", STR_PAD_LEFT);
      $version = implode('', $semver_array);
    }
    if (strpos($version, 'dev') !== FALSE) {
      $version = '0';
    }
    return $version;
  }

}
