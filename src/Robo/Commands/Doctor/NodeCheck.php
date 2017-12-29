<?php

namespace Acquia\Blt\Robo\Commands\Doctor;

/**
 *
 */
class NodeCheck extends DoctorCheck {

  public function performAllChecks() {
    $this->checkNodeVersionFileExists();
  }

  /**
   * Checks that one of .nvmrc or .node-version exists in repo root.
   */
  protected function checkNodeVersionFileExists() {
    if (file_exists($this->getConfigValue('repo.root') . '/.nvmrc')) {
      $this->logProblem(__FUNCTION__, ".nvmrc file exists", 'info');
    }
    elseif (file_exists($this->getConfigValue('repo.root') . '/.node-version')) {
      $this->logProblem(__FUNCTION__, ".node-version file exists", 'info');
    }
    else {
      $this->logProblem(__FUNCTION__, "Neither .nvmrc nor .node-version file found in repo root.", 'comment');
    }
  }

}
