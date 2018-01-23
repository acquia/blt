<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class SimpleSamlPhpTest.
 */
class SimpleSamlPhpTest extends BltProjectTestBase {

  /**
   * Tests recipes:simplesamlphp:init target.
   *
   * Ensures simplesamlphp config files were copied to project.
   */
  public function testSimpleSamlPhpConfigInit() {
    $this->blt("recipes:simplesamlphp:init");
    list($status_code, $output, $config) = $this->blt("source:build:simplesamlphp-config");

    $simpleSamlPhpConfigDirectory = "{$this->sandboxInstance}/simplesamlphp/config";
    $simpleSamlPhpMetadataDirectory = "{$this->sandboxInstance}/simplesamlphp/metadata";

    $this->assertFileExists("$simpleSamlPhpConfigDirectory/authsources.php");
    $this->assertFileExists("$simpleSamlPhpConfigDirectory/config.php");
    $this->assertFileExists("$simpleSamlPhpConfigDirectory/acquia_config.php");

    $configFilePath = "$simpleSamlPhpConfigDirectory/config.php";
    $configFile = file_get_contents($configFilePath);
    $this->assertContains("include 'acquia_config.php';", $configFile);

    $this->assertFileExists("${simpleSamlPhpMetadataDirectory}/saml20-idp-remote.php");
    $this->assertArrayHasKey('simplesamlphp', $config->export());
    $this->assertFileExists("{$this->config->get('docroot')}/simplesaml/saml2");
  }

}
