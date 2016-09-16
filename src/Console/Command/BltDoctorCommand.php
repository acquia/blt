<?php

namespace Acquia\Blt\Console\Command;

use Drupal\Console\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Console\Helper\HelperSet;

class BltDoctorCommand extends ContainerAwareCommand
{

  /**
   * @var OutputInterface
   */
  protected $output;

  /**
   * @var FormatterHelper
   */
  protected $formatter;

  /**
   * DrupliconCommand constructor.
   * @param string       $appRoot
   */
  public function __construct($appRoot) {
    $this->appRoot = $appRoot;
    parent::__construct();
  }
  
  protected function configure()
  {
    $this
      ->setName('blt:doctor')
      ->setAliases(['dr', 'doc', 'doctor'])
      ->setDescription('Check local settings and configuration to ensure that things are set up properly.');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->output = $output;
    $this->formatter = $this->getHelper('formatter');
    $status = $this->getStatus();

    $this->checkAll();
  }

  protected function getStatus() {
    $systemData = $this->getSystemData();
    $connectionData = $this->getConnectionData();
    $themeInfo = $this->getThemeData();
    $directoryData = $this->getDirectoryData();

    $siteData = array_merge(
      $systemData,
      $connectionData,
      $themeInfo,
      $directoryData
    );

    return $siteData;
  }

  /**
   * Performs all checks.
   */
  public function checkAll() {
    $this->checkLocalSettingsFile();
//    $this->checkUriResponse();
//    $this->checkHttps();
//    $this->checkDbConnection();
//    $this->checkCachingConfig();
//    $this->checkNvmExists();
//    $this->checkDatabaseUpdates();
    // @todo Check if Drupal is installed.
    // @todo Check if files directory exists.
    // @todo Check error_level.
    // @todo Check if theme dependencies have been built.
    // @todo Check if composer dependencies have been built.
  }


  /**
   * Checks that local settings file exists.
   */
  protected function checkLocalSettingsFile() {
    if (!file_exists($this->localSettingsPath)) {
      $messages = [
        'Could not find local settings file!',
        "  Your local settings file should exist at $this->localSettingsPath",
      ];
      $this->formatter->formatBlock($messages, 'error');
    }
    else {
      $messages = [
        'Found your local settings file at:',
        "  $this->localSettingsPath",
      ];
      $this->formatter->formatBlock($messages, 'success');
    }
  }

  /**
   * Checks that configured $base_url responds to requests.
   */
  protected function checkUriResponse() {
    $site_available = shell_exec("curl -I --insecure %s", $this->baseUrl);
    if (!$site_available) {
      $messages = [
        "Did not get response from $this->baseUrl",
        "  Is your *AMP stack running?",
        "  Is your \$base_url set correctly in $this->localSettingsPath?",
      ];
      $this->formatter->formatBlock($messages, 'error');
    }
    else {
      $messages = [
        "Received response from site:",
        "   $this->baseUrl",
      ];
      $this->formatter->formatBlock($messages, 'success');
    }
  }
}
