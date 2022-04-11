<?php

namespace Drupal\alshaya_seo_transac;

use Drupal\Core\Render\Element\RenderCallbackInterface;

/**
 * Provides a trusted callback to render gtm attachments.
 */
class AlshayaGtmPreRenderer implements RenderCallbackInterface {

  /**
   * Pre_render callback for 'alshaya_algolia_react_plp' to attach library.
   *
   * @param array $build
   *   The build array.
   *
   * @return array
   *   The altered block build array.
   */
  public static function algoliaPlpAttachmentPreRender(array $build): array {
    if (!_alshaya_seo_process_gtm()) {
      return $build;
    }
    $build['content']['#attached']['library'][] = 'alshaya_seo_transac/gtm_algolia_plp';
    return $build;
  }

}
