<?php

namespace Acquia\Blt\Robo\Commands\Doctor;

/**
 *
 */
class FileSystemCheck extends DoctorCheck {

  public function performAllChecks() {
    $this->checkFileSystem();
  }

  /**
   * Checks that configured file system paths exist and are writable.
   */
  protected function checkFileSystem() {
    $paths = [
      '%files' => 'Public files directory',
      '%private' => 'Private files directory',
      '%temp' => 'Temporary files directory',
    ];

    foreach ($paths as $key => $title) {
      if (empty($this->drushStatus['%paths'][$key])) {
        $this->logProblem(__FUNCTION__ . ":$key", "$title is not set.", 'error');

        continue;
      }

      $path = $this->drushStatus['%paths'][$key];
      if (substr($path, 0, 1) == '/') {
        $full_path = $path;
      }
      else {
        $full_path = $this->getConfigValue('docroot') . "/$path";
      }

      if (file_exists($full_path)) {
        if (!is_writable($full_path)) {
          $this->logProblem(__FUNCTION__ . ":$key", [
            "$title is not writable.",
            "",
            "Change the permissions on $full_path.",
            "Run `chmod 750 $full_path`.",
          ], 'error');
        }
      }
      else {
        $outcome = [
          "$title does not exist.",
          "",
          "Create $full_path.",
        ];

        if (in_array($key, ['%files', '%private'])) {
          $outcome[] = "Installing Drupal will create this directory for you.";
          $outcome[] = "Run `blt drupal:install` to install Drupal, or run `blt setup` to run the entire setup process.";
          $outcome[] = "Otherwise, run `mkdir -p $full_path`.";
          $outcome[] = "";
        }

        $this->logProblem(__FUNCTION__ . ":$key", $outcome, 'error');
      }

    }
  }

}
