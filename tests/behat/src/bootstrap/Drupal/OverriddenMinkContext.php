<?php

namespace Alshaya\BehatContexts;

use Drupal\DrupalExtension\Context\MinkContext;

/**
 * A context for overriding Mink context steps.
 */
class OverriddenMinkContext extends MinkContext {
  /**
   * Opens homepage
   * Example: Given I am on "/"
   * Example: When I go to "/"
   * Example: And I go to "/"
   *
   * @override Given /^(?:|I )am on (?:|the )homepage$/
   * @override When /^(?:|I )go to (?:|the )homepage$/
   */
  public function iAmOnHomepage()
  {
    $this->visitPath('/?behat='  . $this->getBehatSecretKey());
  }

  /**
   * Opens specified page
   * Example: Given I am on "http://batman.com"
   * Example: And I am on "/articles/isBatmanBruceWayne"
   * Example: When I go to "/articles/isBatmanBruceWayne"
   *
   * @override Given /^(?:|I )am on "(?P<page>[^"]+)"$/
   * @override When /^(?:|I )go to "(?P<page>[^"]+)"$/
   */
  public function visit($page)
  {
    $this->visitPath($page . '?behat=' . $this->getBehatSecretKey());
  }

  /**
   * Fetches behat secret key from creds.json file.
   */
  private function getBehatSecretKey()
  {
    static $key = NULL;

    // Avoid file load everytime.
    if (isset($key)) {
      return $key;
    }

    $env_key = getenv('BEHAT_SECRET_KEY');
    if ($env_key) {
      $key = $env_key;
      return $key;
    }

    $filename = 'creds.json';
    $options = getopt('', ['profile:']);
    $profile_arr = explode('-', $options['profile']);
    $env = $profile_arr[2];
    $key = '';
    if (file_exists($filename)) {
      $creds = json_decode(file_get_contents($filename), TRUE);
      $key = $creds[$env]['secret_key'] ?? '';
      return $key;
    }

    print 'Behat secret key not available';
    die();
  }
}
