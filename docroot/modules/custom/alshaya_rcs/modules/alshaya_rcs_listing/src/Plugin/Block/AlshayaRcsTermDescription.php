<?php

namespace Drupal\alshaya_rcs_listing\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a dynamic term description for commerce pages.
 *
 * @Block(
 *   id = "rcs_term_description",
 *   admin_label = @Translation("Alshaya RCS Term Description"),
 *   category = @Translation("RCS Placeholders"),
 * )
 */
class AlshayaRcsTermDescription extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => '<div class="field c-page-title__description rcs-loading"><span class="content">#rcs.category.description#</span></div>',
    ];
  }

}
