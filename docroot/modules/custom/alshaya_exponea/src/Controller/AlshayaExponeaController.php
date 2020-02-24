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
    $config = $this->config(static::EXPONEA_SETTINGS)->get();

    // Remove _core key.
    if (isset($config['_core'])) {
      unset($config['_core']);
    }

    // Remove langcode key.
    if (isset($config['langcode'])) {
      unset($config['langcode']);
    }

    $ret_val = NULL;
    foreach ($config as $key => $value) {
      $ret_val[$key] = $value;
    }

    return new JsonResponse($ret_val);

  }

}
