<?php

namespace Acquia\Blt\Robo\Commands\Validate;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Drupal\Core\Template\TwigTransTokenParser;
use Symfony\Bridge\Twig\Command\LintCommand as TwigLintCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Defines commands in the "tests:twig:lint:all*" namespace.
 */
class TwigCommand extends BltTasks {

  /**
   * Executes Twig validator against all validate.twig.filesets files.
   *
   * @command tests:twig:lint:all
   *
   * @aliases ttla twig tests:twig:lint validate:twig
   */
  public function lintFileSets() {
    $this->say("Validating twig syntax for all custom modules and themes...");

    /** @var \Acquia\Blt\Robo\Filesets\FilesetManager $fileset_manager */
    $fileset_manager = $this->getContainer()->get('filesetManager');
    $fileset_ids = $this->getConfigValue('validate.twig.filesets');
    $filesets = $fileset_manager->getFilesets($fileset_ids);
    $this->executeTwigLintCommandAgainstFilesets($filesets);
  }

  /**
   * Executes Twig validator against a list of files, if in twig.filesets.
   *
   * @command tests:twig:lint:files
   * @aliases ttlf
   *
   * @param string $file_list
   *   A list of files to scan, separated by \n.
   */
  public function lintFileList($file_list) {
    $this->say("Linting twig files...");

    $files = explode("\n", $file_list);

    /** @var \Acquia\Blt\Robo\Filesets\FilesetManager $fileset_manager */
    $fileset_manager = $this->getContainer()->get('filesetManager');
    $fileset_ids = $this->getConfigValue('validate.twig.filesets');
    $filesets = $fileset_manager->getFilesets($fileset_ids);
    foreach ($filesets as $fileset_id => $fileset) {
      $filesets[$fileset_id] = $fileset_manager->filterFilesByFileset($files, $fileset);
    }
    $this->executeTwigLintCommandAgainstFilesets($filesets);
  }

  /**
   * Lints twig against multiple filesets.
   *
   * @param \Symfony\Component\Finder\Finder[] $filesets
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function executeTwigLintCommandAgainstFilesets(array $filesets) {
    $command = $this->createTwigLintCommand();

    /** @var \Acquia\Blt\Robo\Application $application */
    $application = $this->getContainer()->get('application');
    $application->add($command);

    $passed = TRUE;
    $failed_filesets = [];
    foreach ($filesets as $fileset_id => $fileset) {
      if (!is_null($fileset) && iterator_count($fileset)) {
        $this->say("Iterating over fileset $fileset_id...");
        $files = iterator_to_array($fileset);
        $input = new ArrayInput(['filename' => $files]);
        $exit_code = $application->runCommand($command, $input, $this->output());
        if ($exit_code) {
          // We iterate over all filesets before throwing an exception.
          $passed = FALSE;
          $failed_filesets[] = $fileset_id;
        }
      }
      else {
        $this->logger->info("No files were found in fileset $fileset_id. Skipped.");
      }
    }

    if (!$passed) {
      throw new BltException("Linting twig against fileset(s) " . implode(', ', $failed_filesets) . " returned a non-zero exit code.`");
    }

    // If exception wasn't thrown, checks were successful.
    $this->say("All Twig files contain valid syntax.");
  }

  /**
   * Creates the Twig lint command.
   *
   * @return \Symfony\Bridge\Twig\Command\LintCommand
   */
  protected function createTwigLintCommand() {
    $twig = new Environment(new FilesystemLoader());

    $repo_root = $this->getConfigValue('repo.root');
    $extension_file_contents = file_get_contents($repo_root . '/docroot/core/lib/Drupal/Core/Template/TwigExtension.php');

    // Get any custom defined Twig filters to be ignored by linter.
    $twig_filters = (array) $this->getConfigValue('validate.twig.filters');

    // Add Twig filters from Drupal TwigExtension to be ignored.
    $drupal_filters = [];
    if ($matches_count = preg_match_all("#new \\\\Twig_SimpleFilter\('([^']+)',#", $extension_file_contents, $matches)) {
      $drupal_filters = $matches[1];
    }
    $twig_filters = array_merge($twig_filters, $drupal_filters);
    foreach ($twig_filters as $filter) {
      $twig->addFilter(new \Twig_SimpleFilter($filter, function () {}));
    }

    // Get any custom defined Twig functions to be ignored by linter.
    $twig_functions = (array) $this->getConfigValue('validate.twig.functions');

    // Add Twig functions from Drupal TwigExtension to be ignored.
    $drupal_functions = [];
    if ($matches_count = preg_match_all("#new \\\\Twig_SimpleFunction\('([^']+)',#", $extension_file_contents, $matches)) {
      $drupal_functions = $matches[1];
    }
    $twig_functions = array_merge($twig_functions, $drupal_functions);
    foreach ($twig_functions as $function) {
      $twig->addFunction(new \Twig_SimpleFunction($function, function () {}));
    }

    // Add Drupal Twig parser to include trans tag.
    $token_parser_filename = $repo_root . '/docroot/core/lib/Drupal/Core/Template/TwigTransTokenParser.php';
    if (file_exists($token_parser_filename)) {
      require_once $token_parser_filename;
      $twig->addTokenParser(new TwigTransTokenParser());
    }

    $command = new TwigLintCommand();
    $command->setTwigEnvironment($twig);

    return $command;
  }

}
