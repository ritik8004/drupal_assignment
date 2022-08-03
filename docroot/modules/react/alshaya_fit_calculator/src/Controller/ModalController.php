<?php

namespace Drupal\alshaya_fit_calculator\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;

/**
 * Class Modal Controller.
 *
 * @package Drupal\alshaya_fit_calculator\Controller
 */
class ModalController extends ControllerBase {

  /**
   * Callback for modal content.
   */
  public function getModalLinkView(NodeInterface $node) {
    $build = [];
    $content = NULL;
    if ($node instanceof NodeInterface) {
      $content = $this->entityTypeManager()->getViewBuilder('node')->view($node, 'full');
    }
    $build['modal_content'] = [
      '#type' => 'inline_template',
      '#template' => '<div class="modal-content">{{ modal_content }}</div>',
      '#context' => [
        'modal_content' => $content,
      ],
    ];

    return $build;
  }

}
