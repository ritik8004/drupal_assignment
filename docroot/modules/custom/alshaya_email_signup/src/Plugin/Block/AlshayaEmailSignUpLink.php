<?php

namespace Drupal\alshaya_email_signup\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Url;

/**
 * Provides a block to display 'custom link' elements.
 *
 * @Block(
 *   id = "alshaya_email_signup_link",
 *   admin_label = @Translation("Email signup Link")
 * )
 */
class AlshayaEmailSignUpLink extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#type' => 'link',
      '#title' => $this->t('Email sign up'),
      '#url' => Url::fromRoute('<front>'),
      '#options' => [
        'attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => json_encode([
            'width' => 700,
          ]),
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['email-signup-link']);
  }

}
