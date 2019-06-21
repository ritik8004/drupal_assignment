<?php

namespace Drupal\alshaya_seo\PathProcessor;

use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

/**
 * Class AlshayaSeoPathProcessor.
 *
 * @package Drupal\alshaya_seo
 */
class AlshayaSeoPathProcessor implements OutboundPathProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function processOutbound($path,
                                  &$options = [],
                                  Request $request = NULL,
                                  BubbleableMetadata $bubbleable_metadata = NULL) {

    $route = $options['route'] ?? NULL;
    if ($route instanceof Route && $route->getDefault('_disable_route_normalizer')) {
      return $path;
    }

    if (substr($path, -1) !== '/' && strpos($path, '.') === FALSE) {
      $path .= '/';
    }

    return $path;
  }

}
