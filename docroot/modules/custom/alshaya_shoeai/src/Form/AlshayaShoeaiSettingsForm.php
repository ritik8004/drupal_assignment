<?php

namespace Drupal\alshaya_shoeai\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ShoeAI settings form.
 */
class AlshayaShoeaiSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_shoeai.settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_shoeai.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('alshaya_shoeai.settings');
    $form['enable_shoeai'] = [
      '#type' => 'select',
      '#title' => $this->t('Module status'),
      '#required' => TRUE,
      '#options' => [
        '1' => $this->t('Enabled'),
        '0' => $this->t('Disabled'),
      ],
      '#default_value' => $config->get('enable_shoeai') ?: 0,
      '#description' => $this->t('This setting is used for checking if module configs are enabled or disabled.'),
    ];
    $form['shop_id'] = [
      '#type' => 'textfield',
      '#title' => $this
        ->t('Shop Id'),
      '#default_value' => $config->get('shop_id') ?: '',
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
      '#description' => $this->t('shopID config provided by ShoeAI for this site.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_shoeai.settings');
    $config->set('enable_shoeai', $form_state->getValue('enable_shoeai'));
    $config->set('shop_id', $form_state->getValue('shop_id'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

}
