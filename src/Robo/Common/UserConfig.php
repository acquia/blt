<?php

namespace Acquia\Blt\Robo\Common;

use Acquia\Blt\Robo\Blt;

/**
 * Store user preferences.
 */
class UserConfig {

  const OPT_IN_MESSAGE = "🎉 Awesome! Thank you for helping out!";
  const OPT_OUT_MESSAGE = "Ok, no data will be tracked and reported.\nWe take privacy seriously.";

  /**
   * User configuration file path.
   *
   * @var string
   */
  private $configPath;

  /**
   * User configuration.
   *
   * @var array
   */
  private $config;

  /**
   * UserConfig constructor.
   *
   * @param string $configDir
   *   Directory to store user config.
   */
  public function __construct(string $configDir) {
    $this->configPath = $configDir . DIRECTORY_SEPARATOR . 'user.json';
    if (file_exists($this->configPath)) {
      $this->config = json_decode(file_get_contents($this->configPath), TRUE);
    }
    else {
      // Make sure directory tree for user config file exists.
      if (!file_exists($configDir) && !mkdir($configDir, 0777, TRUE)) {
        return;
      }
      // Make sure directory for user config file is writable.
      if (is_writable($configDir) || chmod($configDir, 0777)) {
        $this->setTelemetryUserData();
      }
    }
  }

  /**
   * Check if telemetry preferences are set.
   *
   * @return bool
   *   TRUE if preferences set, FALSE otherwise.
   */
  public function isTelemetrySet() {
    return isset($this->config['telemetry']);
  }

  /**
   * Check if telemetry is enabled.
   *
   * @return bool
   *   TRUE if enabled, FALSE otherwise.
   */
  public function isTelemetryEnabled() {
    return isset($this->config['telemetry']) ? $this->config['telemetry'] : FALSE;
  }

  /**
   * Enable or disable telemetry.
   *
   * @param bool $enabled
   *   Whether to enable or disable telemetry.
   */
  public function setTelemetryEnabled(bool $enabled) {
    $this->config['telemetry'] = $enabled;
    $this->save();
  }

  /**
   * Get telemetry user data.
   *
   * @return array
   *   Telemetry user data.
   */
  public function getTelemetryUserData() {
    $data = $this->config['telemetryUserData'];
    $data['app_version'] = Blt::getVersion();

    return $data;
  }

  /**
   * Initialize telemetry user data.
   */
  public function setTelemetryUserData() {
    $this->config['telemetryUserData'] = [
      'platform' => EnvironmentDetector::getPlatform(),
      'os_name' => EnvironmentDetector::getOsName(),
      'os_version' => EnvironmentDetector::getOsVersion(),
    ];
    $this->save();
  }

  /**
   * Write user config to disk.
   */
  public function save() {
    file_put_contents($this->configPath, json_encode($this->config));
  }

}
