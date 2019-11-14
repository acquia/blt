<?php

namespace Acquia\Blt\Robo\Doctor;

/**
 * BLT Doctor checks for weburi.
 */
class WebUriCheck extends DoctorCheck {

  /**
   * Local site drush yml.
   *
   * @var string
   */
  protected $localSiteDrushYml;

  /**
   * Perform all checks.
   */
  public function performAllChecks() {
    $this->localSiteDrushYml = $this->getConfigValue('docroot') . "/sites/" . $this->getConfigValue('site') . "/local.drush.yml";
    $uri_isset = $this->checkUri();
    if ($uri_isset) {
      $this->checkUriResponse();
      $this->checkHttps();
    }
  }

  /**
   * Check URI.
   *
   * @return bool
   *   Bool.
   */
  protected function checkUri() {
    if (!$this->drushStatus['uri'] || $this->drushStatus['uri'] == 'default') {
      $this->logProblem(__FUNCTION__, [
        "Site URI is not set",
        "Is options.uri set correctly in {$this->localSiteDrushYml}?",
      ], 'error');

      return FALSE;
    }

    return TRUE;
  }

  /**
   * Checks that configured URI responds to requests.
   */
  protected function checkUriResponse() {
    $site_available = $this->getExecutor()->execute("curl -I --insecure " . $this->drushStatus['uri'])->run()->wasSuccessful();
    if (!$site_available) {
      $this->logProblem(__FUNCTION__, [
        "Did not get a response from {$this->drushStatus['uri']}",
        "Is your *AMP stack running?",
        "Is your /etc/hosts file correctly configured?",
        "Is your web server configured to serve this URI from {$this->drushStatus['root']}?",
        "Is options.uri set correctly in {$this->localSiteDrushYml}?",
      ], 'error');
    }
  }

  /**
   * Checks that SSL cert is valid for configured URI.
   */
  protected function checkHttps() {
    if (strstr($this->drushStatus['uri'], 'https')) {
      if (!$this->getExecutor()->execute('curl -cacert ' . $this->drushStatus['uri'])->run()->wasSuccessful()) {
        $this->logProblem(__FUNCTION__, [
          "The SSL certificate for your local site appears to be invalid for {$this->drushStatus['uri']}.",
        ], 'error');
      }
    }
  }

}
