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
 *   admin_label = @Translation("email signup link")
 * )
 */
class AlshayaEmailSignUpLink extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $lang = \Drupal::languageManager()->getCurrentLanguage();
    return [
      '#type' => 'link',
      '#title' => $this->t('email sign up'),
      '#url' => Url::fromUri('internal:/email-sign-up', ['language' => $lang]),
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
