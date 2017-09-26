<?php

namespace Drupal\alshaya_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a block to display 'Site branding' elements.
 *
 * @Block(
 *   id = "alshaya_application_links",
 *   admin_label = @Translation("Application Links")
 * )
 */
class AlshayaApplicationLinks extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'app_android_link' => '',
      'app_apple_link' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form['app_links'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Application links'),
      '#description' => $this->t('Add application links to be rendered.'),
    ];
    $form['app_links']['app_android_link'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Android application'),
      '#description' => $this->t('The android application link.'),
      '#default_value' => $this->configuration['app_android_link'],
    ];
    $form['app_links']['app_apple_link'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Apple application'),
      '#description' => $this->t('The apple application links'),
      '#default_value' => $this->configuration['app_apple_link'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $app_links = $form_state->getValue('app_links');
    $this->configuration['app_android_link'] = $app_links['app_android_link'];
    $this->configuration['app_apple_link'] = $app_links['app_apple_link'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $apps = [];

    // Add the apple application.
    if ($this->configuration['app_apple_link']) {
      $apps[] = [
        '#type' => 'link',
        '#title' => $this->t('Apple Application'),
        '#url' => Url::fromUri($this->configuration['app_apple_link']),
        '#attributes' => [
          'class' => [
            'item-list-application-links__link--apple',
          ],
        ],
      ];
    }

    // Add the android application.
    if ($this->configuration['app_android_link']) {
      $apps[] = [
        '#type' => 'link',
        '#title' => $this->t('Android Application'),
        '#url' => Url::fromUri($this->configuration['app_android_link']),
        '#attributes' => [
          'class' => [
            'item-list-application-links__link--android',
          ],
        ],
      ];
    }

    return [
      '#theme' => 'item_list',
      '#items' => $apps,
      '#attached' => [
        'library' => [
          'core/drupal.dialog.ajax',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['app-links']);
  }

}
