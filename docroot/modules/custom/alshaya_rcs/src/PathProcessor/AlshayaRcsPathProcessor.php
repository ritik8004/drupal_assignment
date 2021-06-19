<?php

namespace Drupal\alshaya_rcs\PathProcessor;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Processes paths and routes internally to dummy nodes.
 *
 *  The dummy node provides helps to provide the default markup for the page.
 * The markup will contain tokens that will be replaced by javascript with
 * the actual data fetched from calling the respective API for the entity.
 */
class AlshayaRcsPathProcessor implements InboundPathProcessorInterface {

  /**
   * {@inheritDoc}
   */
  public function processInbound($path, Request $request) {
    if (strpos($path, '/buy-') > 0) {
      // Hmkw.
      $path = '/node/1875921';
      $request->query->set('page_entity_type', 'product');
      // $path = '/node/115996'; // flsa
    }

    return $path;
  }

}
