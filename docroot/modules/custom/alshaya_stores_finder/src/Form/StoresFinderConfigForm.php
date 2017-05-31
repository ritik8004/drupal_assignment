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
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_stores_finder.settings');
    $enable_disable_search = $form_state->getValue('enable_disable_store_finder_search');
    $config->set('enable_disable_store_finder_search', $enable_disable_search);
    $config->save();

    // Invalidate the cache tag.
    $tags = ['store-finder-cache-tag'];
    Cache::invalidateTags($tags);

    parent::submitForm($form, $form_state);
  }

}
