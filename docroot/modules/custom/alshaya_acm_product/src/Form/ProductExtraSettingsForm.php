<?php

namespace Drupal\alshaya_acm_product\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ProductExtraSettingsForm.
 */
class ProductExtraSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'product_extra_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_acm_product.extra_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_acm_product.extra_settings');
    $config->set('image_thumb_gallery', $form_state->getValue('image_thumb_gallery'));
    $config->set('color_swatches', $form_state->getValue('color_swatches'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_acm_product.extra_settings');

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

    return $form;
  }

}
