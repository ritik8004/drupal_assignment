<?php

namespace Drupal\alshaya_seo\PathProcessor;

use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Symfony\Component\HttpFoundation\Request;

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

    if (substr($path, -1) !== '/' && strpos($path, '.') === FALSE) {
      $path .= '/';
    }

    return $path;
  }

}
