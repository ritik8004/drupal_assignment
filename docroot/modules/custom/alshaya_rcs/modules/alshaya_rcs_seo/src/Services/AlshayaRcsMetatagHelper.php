<?php

namespace Drupal\alshaya_rcs_seo\Services;

/**
 * Class Alshaya RCS Metatag Manager.
 */
class AlshayaRcsMetatagHelper {

  /**
   * Get overriden metatags in rcs.
   *
   * @param array $attachments
   *   An array of metatag objects to be attached to the current page.
   * @param string $url
   *   Metatag url.
   */
  public function getCanonicalMetatags(array &$attachments, $url) {
    foreach ($attachments['#attached']['html_head'] as &$tag) {
      if ($tag[1] === 'canonical_url') {
        $tag[0]['#attributes']['href'] = $url;
      }
      elseif ($tag[1] === 'twitter_cards_page_url') {
        $tag[0]['#attributes']['content'] = $url;
      }
    }
  }

}
