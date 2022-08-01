<?php

namespace Drupal\alshaya_search\Event;

use Drupal\acsf\Event\AcsfEventHandler;

/**
 * Handles Alshaya-specific scrubbing events performed after site duplication.
 */
class AlshayaAcsfDuplicationScrubConfigurationHandler extends AcsfEventHandler {

  /**
   * Implements AcsfEventHandler::handle().
   */
  public function handle() {
    $this->consoleLog(dt('Entered @class', ['@class' => $this::class]));

    $config = \Drupal::configFactory()->getEditable('search_api_solr.settings');

    $site_hash = $config->get('site_hash');

    // If the site has site_hash, it must be re-generated so it uses a different
    // search index from the original one.
    if ($site_hash) {
      global $base_url;

      // This basically uses the same hashing method as in
      // Drupal\search_api_solr\Utility::getSiteHash() method, but it cannot
      // be called directly as it probably uses different namespace for
      // configuration so the variable was not saved to the new site.
      $hash = substr(base_convert(sha1(uniqid($base_url, TRUE)), 16, 36), 0, 6);
      $config->set('site_hash', $hash);

      $config->save();
    }
  }

}
