<?php

namespace Drupal\alshaya_exponea\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Returns responses for Alshaya Exponea routes.
 */
class AlshayaExponeaController extends ControllerBase {

  const EXPONEA_SETTINGS = 'alshaya_exponea.settings';

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

    return new JsonResponse($ret_val);
  }

}
