<?php

namespace Acquia\Blt\Update;

/**
 * Class DoPackagistConverter.
 */
class DoPackagistConverter {

  /**
   * Converts composer.json from packagist.drupal-composer.org to D.O format.
   *
   * @param array $composer_json
   *   The composer.json array.
   *
   * @return array
   *   The modified composer.json array.
   */
  public static function convertComposerJson($composer_json) {
    // Update version constraints in require.
    foreach ($composer_json['require'] as $package_name => $version_constraint) {
      if ($package_name == 'drupal/core') {
        continue;
      }
      if (strstr($package_name, 'drupal/')) {
        $composer_json['require'][$package_name] = self::convertVersionConstraint($version_constraint);
      }
    }

    // Change drupal/lightning to acquia/lightning.
    $composer_json['require']['acquia/lightning'] = '^2';
    unset($composer_json['require']['drupal/lightning']);

    // Update version constraints in require-dev.
    foreach ($composer_json['require-dev'] as $package_name => $version_constraint) {
      if (strstr($package_name, 'drupal/')) {
        $composer_json['require-dev'][$package_name] = self::convertVersionConstraint($version_constraint);
      }
    }

    // Remove https://packagist.drupal-composer.org.
    foreach ($composer_json['repositories'] as $key => $repository) {
      if ($repository['url'] == 'https://packagist.drupal-composer.org') {
        unset($composer_json['repositories'][$key]);
      }
    }

    // Add https://packages.drupal.org/8.
    $composer_json['repositories']['drupal'] = [
      'type' => 'composer',
      'url' => 'https://packages.drupal.org/8',
    ];

    return $composer_json;
  }

  /**
   * Converts from to to packagist.drupal-composer.org to D.O format.
   *
   * @param string $version_constraint
   *   The packagist.drupal-composer.org style version constraint.
   *
   * @return string
   *   The to packages.drupal.org/8 style constraint.
   */
  protected static function convertVersionConstraint($version_constraint) {

    /*
     * 8.2.x-dev => 2.x-dev
     * 8.x-2.x-dev => 2.x-dev
     * 8.2.x-dev#a1b2c3 => 2.x-dev#a1b2c3
     * 8.x-2.x-dev#a1b2c3 => 2.x-dev#a1b2c3
     */
    if (preg_match('/-dev(#[0-9a-f]+)?$/', $version_constraint)) {
      return preg_replace('/^8\.(x-)?/', NULL, $version_constraint);
    }
    /*
     * dev-master => master-dev
     * dev-8.x-1.x => 1.x-dev
     * dev-8.x-1.x#abc123 => 1.x-dev#abc123
     * dev-8.2.x => 2.x-dev
     * dev-8.2.x#abc123 => 2.x-dev#abc123
     * dev-something_else#123abc => something_else-dev#123abc
     */
    if (strpos($version_constraint, 'dev-') === 0) {
      return preg_replace('/^dev-(8\.(x-)?)?([^#]+)(#[a-f0-9]+)?$/', '$3-dev$4', $version_constraint);
    }
    /*
     * 8.* => *
     */
    if (preg_match('/^8\.\*$/', $version_constraint)) {
      return "*";
    }
    /*
     * ~8 => *@stable
     * ^8 => *@stable
     */
    if (preg_match('/^[\^~]8$/', $version_constraint)) {
      return '*@stable';
    }
    /*
     * ~8.1.0-alpha1 > ~1.0.0-alpha1
     * ^8.1.0-alpha1 > ^1.0.0-alpha1
     * ^8.1.0 > ^1.0
     * 8.1.0-alpha1 > 1.0.0-alpha1
     * 8.1.0-beta12 > 1.0.0-beta12
     * 8.12.0-rc22 > 12.0.0-rc22
     */
    if (preg_match('/^([\^~]|[><=!]{1,2})?8(\.)?(\d+)?(\.\d+)?(-(alpha|beta|rc)\d+)?(\.\*)?(@(dev|alpha|beta|rc))?/', $version_constraint, $matches)) {
      /*
       * Group 0. `~8.1.0-alpha1.*@alpha`
       * Group 1. `~`
       * Group 2. `.`
       * Group 3. `1`
       * Group 4. `.0`
       * Group 5. `-alpha1`
       * Group 6. `alpha`
       * Group 7. `.*`
       * Group 8. `@alpha`
       * Group 9. `alpha`
       */
      $new_version_constraint = $matches[3];
      if (isset($matches[4])) {
        $new_version_constraint .= $matches[4];
      }
      if (isset($matches[3])) {
        $new_version_constraint .= '.0';
      }
      else {
        $new_version_constraint .= '*';
      }
      if (isset($matches[5])) {
        $new_version_constraint .= $matches[5];
      }
      elseif (isset($matches[7])) {
        $new_version_constraint .= $matches[7];
      }
      if (isset($matches[8])) {
        $new_version_constraint .= $matches[8];
      }
      if (isset($matches[1])) {
        $new_version_constraint = $matches[1] . $new_version_constraint;
      }
      return $new_version_constraint;
    }
    return $version_constraint;
  }

}
