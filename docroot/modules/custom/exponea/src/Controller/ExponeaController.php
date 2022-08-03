<?php

namespace Drupal\exponea\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Exponea routes.
 */
class ExponeaController extends ControllerBase {

  public const EXPONEA_SETTINGS = 'exponea.settings';

  /**
   * Builds the response.
   */
  public function getManifest() {
    // Get all data stored in configuration.
    $config = $this->config(static::EXPONEA_SETTINGS);

    $ret_val = [
      'name' => $config->get('name'),
      'short_name' => $config->get('short_name'),
      'start_url' => $config->get('start_url'),
      'display' => $config->get('display'),
      'gcm_sender_id' => $config->get('gcm_sender_id'),
    ];

    $response = new CacheableJsonResponse($ret_val);
    // Handle caching.
    $response->addCacheableDependency($config);

    return $response;
  }

}
