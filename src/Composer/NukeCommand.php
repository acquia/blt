<?php

namespace Acquia\Blt\Composer;

use Composer\Util\ProcessExecutor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Composer\Command\BaseCommand;

class NukeCommand extends BaseCommand
{

  protected $commitMessage;
  protected $simulate = false;

  public function configure()
  {
    $this->setName('nuke');
    $this->setDescription("Removes Composer dependencies from disk and re-installs.");
  }

  /**
   * @param \Symfony\Component\Console\Input\InputInterface $input
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *
   * @return int
   */
  public function execute(InputInterface $input, OutputInterface $output)
  {
    $process = new ProcessExecutor($this->getIO());
    $exit_code = $process->execute("rm -rf vendor composer.lock docroot/core docroot/modules/contrib docroot/profiles/contrib docroot/themes/contrib && composer clearcache && composer clearcache --ansi && composer install --ansi", $output);

    return $exit_code;
  }

}
