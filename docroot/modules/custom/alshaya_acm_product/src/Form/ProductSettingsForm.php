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
    $config->set('show_cart_form_in_related', $form_state->getValue('show_cart_form_in_related'));
    $config->set('related_items_size', $form_state->getValue('related_items_size'));
    $config->set('list_view_items_per_page', $form_state->getValue('list_view_items_per_page'));
    $config->set('list_view_auto_page_load_count', $form_state->getValue('list_view_auto_page_load_count'));
    $config->set('brand_logo_base_path', $form_state->getValue('brand_logo_base_path'));
    $config->set('brand_logo_extension', $form_state->getValue('brand_logo_extension'));
    $config->set('all_products_buyable', $form_state->getValue('all_products_buyable'));
    $config->set('not_buyable_message', $form_state->getValue('not_buyable_message'));
    $config->set('not_buyable_help_text', $form_state->getValue('not_buyable_help_text'));
    $config->set('vat_text', $form_state->getValue('vat_text'));
    $config->set('vat_text_footer', $form_state->getValue('vat_text_footer'));
    $config->set('image_slider_position_pdp', $form_state->getValue('image_slider_position_pdp'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_acm_product.settings');

    $form['show_cart_form_in_related'] = [
      '#type' => 'select',
      '#options' => [
        0 => $this->t('no'),
        1 => $this->t('yes'),
      ],
      '#default_value' => $config->get('show_cart_form_in_related'),
      '#title' => $this->t('Show add to cart form in related item blocks'),
      '#description' => $this->t('Show add to cart form in Up sell / Cross sell / Related products blocks.'),
    ];

    $form['related_items_size'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Number of related items to show'),
      '#description' => $this->t('Number of related items to show in Up sell / Cross sell / Related products blocks.'),
      '#required' => TRUE,
      '#default_value' => $config->get('related_items_size'),
    ];

    $form['list_view_items_per_page'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default Number of items to show on Listing pages'),
      '#description' => $this->t('Number of items to show per page for Listing pages like PLP / Search pages. Please clear all caches after updating this.'),
      '#required' => TRUE,
      '#default_value' => $config->get('list_view_items_per_page'),
    ];

    $form['list_view_auto_page_load_count'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Number of pages to load automatically'),
      '#description' => $this->t('Number of pages to load automatically on scroll down, before showing button to load more content.'),
      '#required' => TRUE,
      '#default_value' => $config->get('list_view_auto_page_load_count'),
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

    $form['all_products_buyable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Set all products to be buyable'),
      '#default_value' => $config->get('all_products_buyable'),
    ];

    $form['not_buyable_message'] = [
      '#type' => 'text_format',
      '#format' => $config->get('not_buyable_message.format'),
      '#title' => $this->t('Not-buyable product message'),
      '#default_value' => $config->get('not_buyable_message.value'),
    ];

    $form['not_buyable_help_text'] = [
      '#type' => 'text_format',
      '#format' => $config->get('not_buyable_help_text.format'),
      '#title' => $this->t('Not-buyable product help text'),
      '#default_value' => $config->get('not_buyable_help_text.value'),
    ];

    $form['vat_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('VAT Inclusion text'),
      '#default_value' => $config->get('vat_text'),
    ];

    $form['vat_text_footer'] = [
      '#type' => 'textfield',
      '#title' => $this->t('VAT disclaimer text for the footer'),
      '#default_value' => $config->get('vat_text_footer'),
    ];

    $form['image_slider_position_pdp'] = [
      '#type' => 'select',
      '#title' => $this->t('Image slider position on PDP'),
      '#default_value' => $config->get('image_slider_position_pdp'),
      '#options' => [
        'left' => $this->t('Left'),
        'bottom' => $this->t('Bottom'),
      ],
    ];

    return $form;
  }

}
