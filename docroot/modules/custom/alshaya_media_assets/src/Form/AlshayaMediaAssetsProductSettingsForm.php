<?php

namespace Drupal\alshaya_media_assets\Form;

use Drupal\alshaya_acm_product\Form\ProductSettingsForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Contains Alshaya Media Assets Product Settings Form methods.
 *
 * @package Drupal\alshaya_media_assets\Form
 */
class AlshayaMediaAssetsProductSettingsForm extends ProductSettingsForm {

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return array_merge(parent::getEditableConfigNames(), ['alshaya_media_assets.settings']);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['lp_base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Liquid Pixel Base url'),
      '#description' => $this->t("Base Url for Liquid pixel. No trailing '/'. e.g., https://lp2.hm.com/hmgoepprod"),
      '#default_value' => $this->config('alshaya_media_assets.settings')->get('base_url'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_media_assets.settings')->set('base_url', $form_state->getValue('lp_base_url'))->save();
    parent::submitForm($form, $form_state);
  }

}
