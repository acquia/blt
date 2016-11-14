<?php

namespace Acquia\Blt\Console\Command;

use Symfony\Bridge\Twig\Command\LintCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class TwigLintCommand extends LintCommand
{

  protected function configure() {
    $this->addArgument('environment-dir', InputOption::VALUE_REQUIRED);
    parent::configure();
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $loader = new \Twig_Loader_Filesystem($input->getArgument('environment-dir'));
    $this->setTwigEnvironment(new \Twig_Environment($loader));
    parent::execute($input, $output);
  }
}
