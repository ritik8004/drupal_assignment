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
    return 'alshaya_shoeai_settings_form';
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
      '#title' => $this->t('Shoe AI feature status'),
      '#required' => TRUE,
      '#options' => [
        '1' => $this->t('Enabled'),
        '0' => $this->t('Disabled'),
      ],
      '#default_value' => $config->get('enable_shoeai') ?: 0,
      '#description' => $this->t('This setting is used for checking if shoeAi feature is enabled or disabled.'),
    ];
    $form['shop_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Shop Id'),
      '#default_value' => $config->get('shop_id') ?: '',
      '#size' => 15,
      '#maxlength' => 15,
      '#required' => TRUE,
      '#description' => $this->t('shopID config provided by ShoeAI for this site.'),
    ];
    $form['landing_page_path'] = [
      '#type' => 'textfield',
      '#title' => $this
        ->t('ShoeAI Landing page path'),
      '#default_value' => $config->get('landing_page_path') ?: 'shoeai',
      '#size' => 60,
      '#maxlength' => 128,
      '#description' => $this->t('Enter the path like “shoeai“ or “node/42“ and this would load the ShoeAI
       Loader Script on it. For multiple paths add them comma separated.'),
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
    $config->set('landing_page_path', $form_state->getValue('landing_page_path'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

}
