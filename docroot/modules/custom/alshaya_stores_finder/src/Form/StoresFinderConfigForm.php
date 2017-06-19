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

    $form['pdp_click_collect_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('PDP: Click and Collect title'),
      '#required' => TRUE,
      '#default_value' => $config->get('pdp_click_collect_title'),
    ];

    $form['pdp_click_collect_subtitle'] = [
      '#type' => 'textfield',
      '#title' => $this->t('PDP: Click and Collect sub-title'),
      '#required' => TRUE,
      '#default_value' => $config->get('pdp_click_collect_subtitle'),
    ];

    $form['pdp_click_collect_price'] = [
      '#type' => 'textfield',
      '#title' => $this->t('PDP: Click and Collect price'),
      '#description' => $this->t('Leave blank for free'),
      '#required' => FALSE,
      '#default_value' => $config->get('pdp_click_collect_price'),
    ];

    $form['pdp_click_collect_help_text'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('PDP: Click and Collect help text'),
      '#required' => TRUE,
      '#default_value' => $config->get('pdp_click_collect_help_text.value'),
    ];

    $form['pdp_click_collect_select_option_text'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('PDP: Click and Collect select option text'),
      '#required' => TRUE,
      '#default_value' => $config->get('pdp_click_collect_select_option_text.value'),
    ];

    $form['load_more_item_limit'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Load more item count'),
      '#description' => $this->t('Number of stores after which load more button should be displayed. Click on load more will pull down these number of stores.'),
      '#default_value' => $config->get('load_more_item_limit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_stores_finder.settings');
    $config->set('enable_disable_store_finder_search', $form_state->getValue('enable_disable_store_finder_search'));
    $config->set('pdp_click_collect_title', $form_state->getValue('pdp_click_collect_title'));
    $config->set('pdp_click_collect_subtitle', $form_state->getValue('pdp_click_collect_subtitle'));
    $config->set('pdp_click_collect_price', $form_state->getValue('pdp_click_collect_price'));
    $config->set('pdp_click_collect_help_text', $form_state->getValue('pdp_click_collect_help_text'));
    $config->set('pdp_click_collect_select_option_text', $form_state->getValue('pdp_click_collect_select_option_text'));
    $config->set('load_more_item_limit', $form_state->getValue('load_more_item_limit'));
    $config->save();

    // Invalidate the cache tag.
    $tags = ['store-finder-cache-tag'];
    Cache::invalidateTags($tags);

    parent::submitForm($form, $form_state);
  }

}
