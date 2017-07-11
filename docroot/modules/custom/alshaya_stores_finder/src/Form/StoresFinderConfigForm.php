<?php

namespace Drupal\alshaya_stores_finder\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class StoresFinderConfigForm.
 */
class StoresFinderConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_stores_finder_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_stores_finder.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_stores_finder.settings');

    $form['enable_disable_store_finder_search'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable or disable store finder search on site'),
      '#required' => TRUE,
      '#default_value' => $config->get('enable_disable_store_finder_search'),
      '#options' => [0 => $this->t('Disable'), 1 => $this->t('Enable')],
    ];

    $form['load_more_item_limit'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Load more item count'),
      '#description' => $this->t('Number of stores after which load more button should be displayed. Click on load more will pull down these number of stores.'),
      '#default_value' => $config->get('load_more_item_limit'),
    ];

    $form['search_proximity_radius'] = [
      '#type' => 'number',
      '#min' => 1,
      '#step' => 1,
      '#title' => $this->t('Store finder proximity radius'),
      '#description' => $this->t('Proximity radius for store search. This will be in KM.'),
      '#default_value' => $config->get('search_proximity_radius'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_stores_finder.settings');
    $config->set('enable_disable_store_finder_search', $form_state->getValue('enable_disable_store_finder_search'));
    $config->set('load_more_item_limit', $form_state->getValue('load_more_item_limit'));
    $config->set('search_proximity_radius', $form_state->getValue('search_proximity_radius'));
    $config->save();

    // Invalidate the cache tag.
    $tags = ['store-finder-cache-tag'];
    Cache::invalidateTags($tags);

    parent::submitForm($form, $form_state);
  }

}
