<?php

namespace Drupal\alshaya_acm_product\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Views;
use Drupal\Core\Cache\Cache;

/**
 * Class AlshayaListingSettingsForm.
 */
class AlshayaListingSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_acm_listing_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_acm_product.listing_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_acm_product.listing_settings');
    $config->set('filter_oos_product', $form_state->getValue('filter_oos_product'));
    $config->save();
    $this->invalidateCacheTags();
    return parent::submitForm($form, $form_state);
  }

  /**
   * Invalidate cache tags.
   */
  protected function invalidateCacheTags() {
    $view = Views::getView('alshaya_product_list');
    $view->setDisplay('block_1');
    $tags = $view->getCacheTags();
    $view->setDisplay('block_2');
    $tags = array_unique(array_merge($tags, $view->getCacheTags()));

    $view = Views::getView('search');
    $view->setDisplay('page');
    $tags = array_unique(array_merge($tags, $view->getCacheTags()));

    Cache::invalidateTags($tags);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_acm_product.listing_settings');

    $form['filter_oos_product'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display out of stock product.'),
      '#default_value' => $config->get('filter_oos_product'),
    ];

    return $form;
  }

}
