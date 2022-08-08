<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\node\NodeInterface;

/**
 * Provides a resource to get magazine detail deeplink.
 *
 * @RestResource(
 *   id = "magazine_detail_page_v3",
 *   label = @Translation("Magazine Detail Page"),
 *   uri_paths = {
 *     "canonical" = "/rest/v3/page/magazine-detail"
 *   }
 * )
 */
class MagazineDetailPageV3 extends MagazineDetailPage {

  /**
   * Get shop the story product skus.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Magazine detail node.
   *
   * @return array
   *   Returns product skus for shop the story.
   */
  protected function getShopTheStory(NodeInterface $node) {
    $shop_the_story = [];
    $skus = $node->get('field_magazine_shop_the_story')->getValue();
    $shop_the_story['skus'] = array_map(fn($value) => $value['value'], $skus);

    return $shop_the_story;
  }

}
