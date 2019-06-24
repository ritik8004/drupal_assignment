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
    // Avoid urls that have _disable_route_normalizer.
    if ($route instanceof Route && $route->getDefault('_disable_route_normalizer')) {
      return $path;
    }

    // Avoid rest urls.
    if (strpos($path, '/rest/') > -1 || strpos($path, '/oauth/') > -1) {
      return $path;
    }

    // Avoid urls already having trailing slash.
    if (substr($path, -1) === '/') {
      return $path;
    }

    // Avoid urls with dot in alias .html / .txt / etc.
    if (strpos($path, '.') > -1) {
      return $path;
    }

    return $path . '/';
  }

}
