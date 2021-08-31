<?php

namespace Drupal\alshaya_rcs_listing\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a dynamic LHN Block for commerce pages.
 *
 * @Block(
 *   id = "rcs_ph_lhn",
 *   admin_label = @Translation("RCS Placeholders LHN"),
 *   category = @Translation("RCS Placeholders"),
 * )
 */
class RcsPhLhnBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $build['wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'rcs-ph-lhn_block',
        'data-param-get-data' => 'false',
        'class' => ['block-alshaya-category-lhn-block'],
        'data-param-entity-to-get' => 'navigation_menu',
      ],
    ];

    $build['wrapper']['content'] = [
      '#theme' => 'alshaya_rcs_lhn_tree',
      '#lhn_cat_tree' => [
        // Clickable.
        [
          'lhn' => 1,
          'label' => '#rcs.lhn.name#',
          'url' => '#rcs.lhn.url_path#',
          'depth' => '#rcs.lhn.level#',
          'active' => '#rcs.lhn.active#',
          'clickable' => TRUE,
        ],
        // Unclickable.
        [
          'lhn' => 1,
          'label' => '#rcs.lhn.name#',
          'url' => '#rcs.lhn.url_path#',
          'depth' => '#rcs.lhn.level#',
          'active' => '#rcs.lhn.active#',
          'clickable' => FALSE,
        ],
      ],
    ];

    return $build;
  }

}
