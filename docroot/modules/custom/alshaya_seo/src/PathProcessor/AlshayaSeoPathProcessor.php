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

    // Remove trailing slash from all urls.
    // We want it on category pages so we do not have this forced in htaccess.
    // We force it from here.
    $path = rtrim($path, '/');

    // Add trailing slash for home page.
    if (empty($path)) {
      $path = '/';
    }
    // Add trailing slash for term pages.
    elseif ($route instanceof Route && $route->getPath() == '/taxonomy/term/{taxonomy_term}') {
      $path = $path . '/';
    }

    return $path;
  }

}
