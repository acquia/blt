<?php

namespace Acquia\Blt\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 */
class ConfigurePhantomJsCommand extends BaseCommand {

  /**
   * ${inheritdoc}.
   */
  protected function configure() {
    $this
      ->setName('configure:phantomjs')
      ->setDescription('Configure PhantomJs')
      ->addArgument(
        'repo-root',
        InputArgument::REQUIRED,
        'The root directory for the repository.'
      );
  }

  /**
   * ${inheritdoc}.
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $repo_root = $input->getArgument('repo-root');
    $composer_json = json_decode(file_get_contents($repo_root . '/composer.json'), TRUE);
    $composer_json['scripts']['install-phantomjs'] = [
      "rm -rf vendor/bin/phantomjs",
      "PhantomInstaller\\Installer::installPhantomJS",
    ];
    file_put_contents($repo_root . '/composer.json', json_encode($composer_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
  }

}
