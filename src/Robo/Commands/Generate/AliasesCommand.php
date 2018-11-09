<?php

namespace Acquia\Blt\Robo\Commands\Generate;

use Acquia\Blt\Robo\BltTasks;
use AcquiaCloudApi\CloudApi\Client;
use AcquiaCloudApi\CloudApi\Connector;
use Symfony\Component\Yaml\Yaml;
use Acquia\Blt\Robo\Common\YamlMunge;

/**
 * Defines commands in the "generate:aliases" namespace.
 */
class AliasesCommand extends BltTasks {

  /**
   * @var \AcquiaCloudApi\CloudApi\Client
   */
  protected $cloudApiClient;

  /**
   * @var string
   */
  protected $appId;

  /**
   * @var string
   */
  protected $cloudConfDir;

  /**
   * @var string
   */
  protected $cloudConfFileName;

  /**
   * @var string
   */
  protected $cloudConfFilePath;

  /**
   * @var string
   */
  protected $siteAliasDir;

  /**
   * Generates new Acquia site aliases for Drush.
   *
   * @command recipes:aliases:init:acquia
   *
   * @aliases raia aliases
   *
   */
  public function generateAliasesAcquia() {
    $this->cloudConfDir = $_SERVER['HOME'] . '/.acquia';
    $this->setAppId();
    $this->cloudConfFileName = 'cloud_api.conf';
    $this->cloudConfFilePath = $this->cloudConfDir . '/' . $this->cloudConfFileName;
    $this->siteAliasDir = $this->getConfigValue('drush.alias-dir');

    $cloudApiConfig = $this->loadCloudApiConfig();
    $this->setCloudApiClient($cloudApiConfig['key'], $cloudApiConfig['secret']);

    $this->say("<info>Gathering site info from Acquia Cloud.</info>");
    $site = $this->cloudApiClient->application($this->appId);

    $error = FALSE;
    try {
      $this->getSiteAliases($site);
    }
    catch (\Exception $e) {
      $error = TRUE;
      $this->logger->error("Did not write aliases for $site->name. Error: " . $e->getMessage());
    }
    if (!$error) {
      $this->say("<info>Aliases were written, type 'drush sa' to see them.</info>");
    }
  }

  /**
   * Sets the Acquia application ID from config and prompt.
   */
  protected function setAppId() {
    if ($app_id = $this->getConfigValue('cloud.appId')) {
      $this->appId = $app_id;
    }
    else {
      $this->say("<info>To generate an alias for the Acquia Cloud, BLT require's your Acquia Cloud application ID.</info>");
      $this->say("<info>See https://docs.acquia.com/acquia-cloud/manage/applications.</info>");
      $this->appId = $this->askRequired('Please enter your Acquia Cloud application ID');
      $this->writeAppConfig($this->appId);
    }
  }

  /**
   * Sets appId value in blt.yml to disable interative prompt.
   *
   * @param string $app_id
   *  The Acquia Cloud application UUID.
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function writeAppConfig($app_id) {

    $project_yml = $this->getConfigValue('blt.config-files.project');
    $this->say("Updating ${project_yml}...");
    $project_config = YamlMunge::parseFile($project_yml);
    $project_config['cloud']['appId'] = $app_id;
    try {
      YamlMunge::writeFile($project_yml, $project_config);
    }
    catch (\Exception $e) {
      throw new BltException("Unable to update $project_yml.");
    }
  }

  /**
   * Loads CloudAPI token from an user input if it doesn't exist on disk.
   *
   * @return array
   *   An array of CloudAPI token configuration.
   */
  protected function loadCloudApiConfig() {
    if (!$config = $this->loadCloudApiConfigFile()) {
      $config = $this->askForCloudApiCredentials();
    }
    return $config;
  }

  /**
   * Load existing credentials from disk.
   *
   * @return bool|array
   *   Returns credentials as array on success, or FALSE on failure.
   */
  protected function loadCloudApiConfigFile() {
    if (file_exists($this->cloudConfFilePath)) {
      return (array) json_decode(file_get_contents($this->cloudConfFilePath));
    }
    else {
      return FALSE;
    }
  }

  /**
   * Interactive prompt to get Cloud API credentials.
   *
   * @return array
   *   Returns credentials as array on success.
   */
  protected function askForCloudApiCredentials() {
    $this->say("You may generate new API tokens at <comment>https://cloud.acquia.com/app/profile/tokens</comment>");
    $key = $this->askRequired('Please enter your Acquia cloud API key:');
    $secret = $this->askRequired('Please enter your Acquia cloud API secret:');
    do {
      $this->setCloudApiClient($key, $secret);
      $cloud_api_client = $this->getCloudApiClient();
    } while (!$cloud_api_client);
    $config = array(
      'key' => $key,
      'secret' => $secret,
    );
    $this->writeCloudApiConfig($config);
    return $config;
  }

  /**
   * Writes configuration to local file.
   *
   * @param array $config
   *   An array of CloudAPI configuraton.
   */
  protected function writeCloudApiConfig(array $config) {
    if (!is_dir($this->cloudConfDir)) {
      mkdir($this->cloudConfDir);
    }
    file_put_contents($this->cloudConfFilePath, json_encode($config));
    $this->say("Credentials were written to {$this->cloudConfFilePath}.");
  }

  /**
   * Tests CloudAPI client authentication credentials.
   *
   * @param string $key
   *   The Acquia token public key.
   * @param string $secret
   *   The Acquia token secret key.
   *
   * @return array
   *   Returns credentials as array on success, or NULL on failure.
   */
  protected function setCloudApiClient($key, $secret) {
    try {
      $connector = new Connector(array(
        'key' => $key,
        'secret' => $secret,
      ));
      $cloud_api = Client::factory($connector);
      // We must call some method on the client to test authentication.
      $cloud_api->applications();
      $this->cloudApiClient = $cloud_api;
      return $cloud_api;
    }
    catch (\Exception $e) {
      // @todo this is being thrown after first auth. still works? check out.
      $this->logger->error('Failed to authenticate with Acquia Cloud API.');
      $this->logger->error('Exception was thrown: ' . $e->getMessage());
      return NULL;
    }
  }

  /**
   * Gets connection with API client.
   *
   * @return \AcquiaCloudApi\CloudApi\Client
   *   The API Client connection.
   */
  protected function getCloudApiClient() {
    return $this->cloudApiClient;
  }

  /**
   * Gets generated drush site aliases.
   *
   * @param string $site
   *   The Acquia subscription that aliases will be generated for.
   *
   * @throws \Exception
   */
  protected function getSiteAliases($site) {
    /** @var \AcquiaCloudApi\Response\ApplicationResponse $site */
    $aliases = [];
    $sites = [];
    $this->output->writeln("<info>Gathering sites list from Acquia Cloud.</info>");

    $environments = $this->cloudApiClient->environments($site->uuid);
    $hosting = $site->hosting->type;
    $site_split = explode(':', $site->hosting->id);

    foreach ($environments as $env) {
      $domains = [];
      $domains = $env->domains;
      $this->say('<info>Found ' . count($domains) . ' sites for environment ' . $env->name . ', writing aliases...</info>');

      $sshFull = $env->sshUrl;
      $ssh_split = explode('@', $env->sshUrl);
      $envName = $env->name;
      $remoteHost = $ssh_split[1];
      $remoteUser = $ssh_split[0];

      if ($hosting == 'ace') {

        $siteID = $site_split[1];
        $uri = $env->domains[0];
        $sites[$siteID][$envName] = ['uri' => $uri];
        $siteAlias = $this->getAliases($uri, $envName, $remoteHost, $remoteUser, $siteID);
        $sites[$siteID][$envName] = $siteAlias[$envName];

      }

      if ($hosting == 'acsf') {
        $this->say('<info>ACSF project detected - generating sites data....</info>');

        try {
          $acsf_sites = $this->getSitesJson($sshFull, $remoteUser);
        }
        catch (\Exception $e) {
          $this->logger->error("Could not fetch acsf data for $envName. Error: " . $e->getMessage());
        }

        // Look for list of sites and loop over it.
        if ($acsf_sites) {
          foreach ($acsf_sites['sites'] as $name => $info) {

            // Reset uri value to identify non-primary domains.
            $uri = NULL;

            // Get site prefix from main domain.
            if (strpos($name, '.acsitefactory.com')) {
              $acsf_site_name = explode('.', $name, 2);
              $siteID = $acsf_site_name[0];
            }
            if (!empty($siteID) && !empty($info['flags']['preferred_domain'])) {
              $uri = $name;
            }

            foreach ($domains as $site) {
              // Skip sites without primary domain as the alias will be invalid.
              if (isset($uri)) {
                $sites[$siteID][$envName] = ['uri' => $uri];
                $siteAlias = $this->getAliases($uri, $envName, $remoteHost, $remoteUser, $siteID);
                $sites[$siteID][$envName] = $siteAlias[$envName];
              } continue;
            }
          }

        }
      }

    }

    // Write the alias files to disk.
    foreach ($sites as $siteID => $aliases) {
      $this->writeSiteAliases($siteID, $aliases);

    }
  }

  /**
   * Generates a site alias for valid domains.
   *
   * @param string $uri
   *   The unique site url.
   * @param string $envName
   *   The current environment.
   * @param string $remoteHost
   *   The remote host.
   * @param string $remoteUser
   *   The remote user.
   * @param string $siteID
   *   The siteID / group.
   *
   * @return array
   *   The full alias for this site.
   */
  protected function getAliases($uri, $envName, $remoteHost, $remoteUser, $siteID) {
    $alias = array();
    // Skip wildcard domains.
    $skip_site = FALSE;
    if (strpos($uri, ':*') !== FALSE) {
      $skip_site = TRUE;
    }

    if (!$skip_site) {
      $docroot = '/var/www/html/' . $remoteUser . '/docroot';
      $alias[$envName]['uri'] = $uri;
      $alias[$envName]['host'] = $remoteHost;
      $alias[$envName]['options'] = [];
      $alias[$envName]['paths'] = ['dump-dir' => '/mnt/tmp'];
      $alias[$envName]['root'] = $docroot;
      $alias[$envName]['user'] = $remoteUser;
      $alias[$envName]['ssh'] = ['options' => '-p 22'];

      return $alias;

    }
  }

  /**
   * Gets ACSF sites info without secondary API calls or Drupal bootstrap.
   *
   * @param string $sshFull
   *   The full ssh connection string for this environment.
   * @param string $remoteUser
   *   The site.env remoteUser string used in the remote private files path.
   *
   * @return array
   *   An array of ACSF site data for the current environment.
   */
  protected function getSitesJson($sshFull, $remoteUser) {

    $this->say('Getting ACSF sites.json information...');
    $result = $this->taskRsync()

      ->fromPath('/mnt/files/' . $remoteUser . '/files-private/sites.json')
      ->fromHost($sshFull)
      ->toPath($this->cloudConfDir)
      ->remoteShell('ssh -A -p 22')
      ->run();

    if (!$result->wasSuccessful()) {
      throw new \Exception("Unable to rsync ACSF sites.json");
    }

    $fullPath = $this->cloudConfDir . '/sites.json';
    $response_body = file_get_contents($fullPath);
    $sites_json = json_decode($response_body, TRUE);

    return $sites_json;

  }

  /**
   * Writes site aliases to disk.
   *
   * @param string $site_id
   *   The siteID or alias group.
   * @param array $aliases
   *   The alias array for this site group.
   *
   * @return string
   *   The alias site group file path.
   *
   * @throws \Exception
   */
  protected function writeSiteAliases($site_id, array $aliases) {

    if (!is_dir($this->siteAliasDir)) {
      mkdir($this->siteAliasDir);
    }
    $filePath = $this->siteAliasDir . '/' . $site_id . '.site.yml';
    if (file_exists($filePath)) {
      if (!$this->confirm("File $filePath already exists and will be overwritten. Continue?")) {
        throw new \Exception("Aborted at user request");
      }
    }

    file_put_contents($filePath, Yaml::dump($aliases));
    return $filePath;
  }

}
