<?php

namespace Drupal\alshaya_acm_product\Form;

use Drupal\alshaya_acm_product\Service\SkuPriceHelper;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Product Display Settings Form.
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
    $config->set('price_display_mode', $form_state->getValue('price_display_mode'));
    $config->set('show_color_images_on_filter', (int) $form_state->getValue('show_color_images_on_filter'));
    $config->save();

    return parent::submitForm($form, $form_state);
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

    $form['show_color_images_on_filter'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show images in gallery on listing pages from filtered color.'),
      '#default_value' => $config->get('show_color_images_on_filter'),
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

    $form['price_display_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Price display mode'),
      '#default_value' => $config->get('price_display_mode') ?? SkuPriceHelper::PRICE_DISPLAY_MODE_SIMPLE,
      '#options' => [
        SkuPriceHelper::PRICE_DISPLAY_MODE_SIMPLE => $this->t('Simple / Default'),
        SkuPriceHelper::PRICE_DISPLAY_MODE_FROM_TO => $this->t('From - To'),
      ],
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
