<?php

namespace Drupal\alshaya_stores_finder\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Cache\Cache;

/**
 * Provides stores finder block.
 *
 * @Block(
 *   id = "alshaya_stores_finder",
 *   admin_label = @Translation("Alshaya stores finder")
 * )
 */
class StoresFinderBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'link_title' => $this->t('Find Store'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['link_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link title'),
      '#description' => $this->t('Title to be displayed for the link.'),
      '#default_value' => $this->configuration['link_title'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['link_title'] = $form_state->getValue('link_title');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $is_active = '';
    // Current route name.
    $current_route = \Drupal::routeMatch()->getRouteName();
    // If current page, add class.
    if ($current_route == 'view.stores_finder.page_2') {
      $is_active = 'is-active';
    }

    return [
      '#markup' => Link::createFromRoute($this->configuration['link_title'], 'view.stores_finder.page_2', [], [
        'attributes' =>
          [
            'class' => ['stores-finder', $is_active],
          ],
      ])->toString(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

}
