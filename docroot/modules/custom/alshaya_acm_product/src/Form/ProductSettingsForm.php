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
    $config->set('related_items_size', $form_state->getValue('related_items_size'));
    $config->set('brand_logo_base_path', $form_state->getValue('brand_logo_base_path'));
    $config->set('brand_logo_extension', $form_state->getValue('brand_logo_extension'));
    $config->set('not_buyable_message', $form_state->getValue('not_buyable_message'));
    $config->set('not_buyable_help_text', $form_state->getValue('not_buyable_help_text'));
    $config->set('size_guide_link', $form_state->getValue('size_guide_link'));
    $config->set('size_guide_modal_content', $form_state->getValue('size_guide_modal_content'));
    $config->set('vat_text', $form_state->getValue('vat_text'));
    $config->set('vat_text_footer', $form_state->getValue('vat_text_footer'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_acm_product.settings');

    $form['related_items_size'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Number of related items to show'),
      '#description' => $this->t('Number of related items to show in Up sell / Cross sell / Related products blocks.'),
      '#required' => TRUE,
      '#default_value' => $config->get('related_items_size'),
    ];

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

    $form['not_buyable_message'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Not-buyable product message'),
      '#default_value' => $config->get('not_buyable_message.value'),
    ];

    $form['not_buyable_help_text'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Not-buyable product help text'),
      '#default_value' => $config->get('not_buyable_help_text.value'),
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

    $form['vat_text'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('VAT Inclusion text'),
      '#default_value' => $config->get('vat_text.value'),
    ];

    $form['vat_text_footer'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('VAT disclaimer text for the footer'),
      '#default_value' => $config->get('vat_text_footer.value'),
    ];

    return $form;
  }

}
