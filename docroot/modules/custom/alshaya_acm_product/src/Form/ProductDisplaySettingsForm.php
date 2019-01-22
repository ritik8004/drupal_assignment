<?php

namespace Drupal\alshaya_acm_product\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Views;

/**
 * Class ProductDisplaySettingsForm.
 */
class ProductDisplaySettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'product_display_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_acm_product.display_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_acm_product.display_settings');
    $config->set('image_thumb_gallery', $form_state->getValue('image_thumb_gallery'));
    $config->set('color_swatches', $form_state->getValue('color_swatches'));
    $config->set('color_swatches_hover', $form_state->getValue('color_swatches_hover'));
    $config->set('short_desc_characters', $form_state->getValue('short_desc_characters'));
    $config->set('short_desc_text_summary', $form_state->getValue('short_desc_text_summary'));
    $config->set('display_oos_product', $form_state->getValue('display_oos_product'));
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

    $config = $this->config('alshaya_acm_product.display_settings');

    $form['image_thumb_gallery'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display image thumb gallery on product node.'),
      '#default_value' => $config->get('image_thumb_gallery'),
    ];

    $form['color_swatches'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display color swatches on product node.'),
      '#default_value' => $config->get('color_swatches'),
    ];

    $form['color_swatches_hover'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Apply hover effect on color swatches.'),
      '#default_value' => $config->get('color_swatches_hover'),
    ];

    $form['display_oos_product'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display out of stock product.'),
      '#default_value' => $config->get('display_oos_product'),
    ];

    $form['short_desc'] = [
      '#type' => 'details',
      '#title' => $this->t('PDP short description settings'),
      '#tree' => FALSE,
      '#open' => TRUE,
    ];

    $form['short_desc']['short_desc_text_summary'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use text_summary to generate short description.'),
      '#default_value' => $config->get('short_desc_text_summary'),
    ];

    $form['short_desc']['short_desc_characters'] = [
      '#type' => 'number',
      '#title' => $this->t('No. of characters that should be displayed as short decription on PDP page.'),
      '#default_value' => $config->get('short_desc_characters'),
    ];
    return $form;
  }

}
