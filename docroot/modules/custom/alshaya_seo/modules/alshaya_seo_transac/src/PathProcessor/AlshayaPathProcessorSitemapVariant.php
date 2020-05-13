<?php

namespace Drupal\alshaya_seo_transac\PathProcessor;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Class PathProcessorSitemapVariant.
 */
class AlshayaPathProcessorSitemapVariant implements InboundPathProcessorInterface, OutboundPathProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {
    $path = preg_replace_callback('/sitemap(-(\d+))?\.xml/', function ($matches) use ($request) {
      if (isset($matches[2])) {
        $request->query->set('page', $matches[2]);
      }
      return 'sitemap.xml';
    }, $path);

    return $path;
  }

  /**
   * {@inheritdoc}
   */
  public function processOutbound($path, &$options = [], Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {
    return $path;
  }

}
