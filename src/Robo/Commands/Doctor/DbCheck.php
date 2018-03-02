<?php

namespace Acquia\Blt\Robo\Commands\Doctor;

/**
 *
 */
class DbCheck extends DoctorCheck {

  public function performAllChecks() {
    if ($this->checkRequiredKeys()) {
      $this->checkDbConnection();
    }
  }

  /**
   * Check that required drush status keys exist.
   *
   * @return bool
   */
  protected function checkRequiredKeys() {
    $keys = [
      'db-hostname',
      'db-username',
      'db-password',
      'db-name',
      'db-port',
    ];
    $outcome = [];
    foreach ($keys as $key) {
      if (!array_key_exists($key, $this->drushStatus)) {
        $outcome[] = "drush status is missing the '$key' key.";
        $this->logProblem(__FUNCTION__, $outcome, 'error');

        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Checks that drush is able to connect to database.
   */
  protected function checkDbConnection() {
    $connection = @mysqli_connect(
      $this->drushStatus['db-hostname'],
      $this->drushStatus['db-username'],
      $this->drushStatus['db-password'],
      $this->drushStatus['db-name'],
      $this->drushStatus['db-port']
    );

    if ($connection) {
      mysqli_close($connection);
      return TRUE;
    }

    $outcome = [
      'Could not connect to MySQL database.',
      "",
      "Is your *AMP stack running?",
      'Are your database credentials correct?',
      "  db-driver: {$this->drushStatus['db-driver']}",
      "  db-hostname: {$this->drushStatus['db-hostname']}",
      "  db-username: {$this->drushStatus['db-username']}",
      "  db-password: {$this->drushStatus['db-password']}",
      "  db-name: {$this->drushStatus['db-name']}",
      "  db-port: {$this->drushStatus['db-port']}",
      "",
    ];

    if ($this->drushStatus['db-driver'] == 'mysql') {
      $outcome[] = "To verify your mysql credentials, run `mysql -u {$this->drushStatus['db-username']} -h {$this->drushStatus['db-hostname']} -p{$this->drushStatus['db-password']} -P {$this->drushStatus['db-port']}`";
      $outcome[] = "";
    }

    $php_conf = is_array($this->drushStatus['php-conf']) ? implode(', ', $this->drushStatus['php-conf']) : $this->drushStatus['php-conf'];
    $outcome = array_merge($outcome, [
      'Are you using the correct PHP binary?',
      'Is PHP using the correct MySQL socket?',
      "  php-os: {$this->drushStatus['php-os']}",
      "  php-bin: {$this->drushStatus['php-bin']}",
      "  php-conf: $php_conf",
      '',
      'Are you using the correct site and settings.php file?',
      "  site: {$this->drushStatus["site"]}",
      "  drupal-settings-file: {$this->drushStatus["drupal-settings-file"]}",
      "",
      "To verify, run `drush sqlc`",
      "",
    ]);

    $this->logProblem(__FUNCTION__, $outcome, 'error');
  }

}
