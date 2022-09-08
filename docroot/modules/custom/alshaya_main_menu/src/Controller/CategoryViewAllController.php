<?php

namespace Drupal\alshaya_main_menu\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\taxonomy\TermInterface;

/**
 * Controller for Category View All.
 *
 * @package Drupal\alshaya_main_menu\Controller
 */
class CategoryViewAllController extends ControllerBase {

  /**
   * Get view builder for given term.
   *
   * @param \Drupal\taxonomy\TermInterface $taxonomy_term
   *   Taxonomy term.
   */
  public function getBuild(TermInterface $taxonomy_term) {
    // Render empty content as view-all listing is displayed by algolia block.
    return [
      '#cache' => [
        'tags' => $taxonomy_term->getCacheTags(),
      ],
    ];
  }

  /**
   * Route title callback.
   *
   * @return string
   *   The title for view all page.
   */
  public function getTitle() {
    return $this->t('View All');
  }

}
