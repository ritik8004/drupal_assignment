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
    drush_print(dt('Entered @class', ['@class' => get_class($this)]));

    $config = \Drupal::configFactory()->getEditable('search_api_solr.settings');

    $site_hash = $config->get('site_hash');

    if ($site_hash) {
      global $base_url;
      $hash = substr(base_convert(sha1(uniqid($base_url, TRUE)), 16, 36), 0, 6);
      $config->set('site_hash', $hash);

      $config->save();
    }
  }

}
