<?php

namespace Drupal\alshaya_acm_product\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ProductSettingsForm.
 */
class ProductSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'product_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_acm_product.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_acm_product.settings');
    $config->set('brand_logo_base_path', $form_state->getValue('brand_logo_base_path'));
    $config->set('brand_logo_extension', $form_state->getValue('brand_logo_extension'));
    $config->set('size_guide_link', $form_state->getValue('size_guide_link'));
    $config->set('size_guide_modal_content', $form_state->getValue('size_guide_modal_content'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_acm_product.settings');

    $form['brand_logo_base_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base path on server for Brand Logo'),
      '#description' => $this->t('Do not include trailing or leading slashes.'),
      '#required' => TRUE,
      '#default_value' => $config->get('brand_logo_base_path'),
    ];

    $form['brand_logo_extension'] = [
      '#type' => 'textfield',
      '#title' => $this->t('File extension for Brand Logo'),
      '#description' => $this->t('Do not include leading dots.'),
      '#required' => TRUE,
      '#default_value' => $config->get('brand_logo_extension'),
    ];

    $form['size_guide_link'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable Size Guide link'),
      '#required' => TRUE,
      '#default_value' => $config->get('size_guide_link'),
      '#options' => [0 => $this->t('Disable'), 1 => $this->t('Enable')],
    ];

    $form['size_guide_modal_content'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Size guide content'),
      '#default_value' => $config->get('size_guide_modal_content.value'),
    ];

    return $form;
  }

}
