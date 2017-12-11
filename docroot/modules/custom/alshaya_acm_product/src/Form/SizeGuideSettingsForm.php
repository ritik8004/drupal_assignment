<?php

namespace Drupal\alshaya_acm_product\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SizeGuideSettingsForm.
 */
class SizeGuideSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'size_guide_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_acm_product.size_guide'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_acm_product.size_guide');
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
    $config = $this->config('alshaya_acm_product.size_guide');

    $form['size_guide_link'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable Size Guide link'),
      '#required' => TRUE,
      '#default_value' => $config->get('size_guide_link'),
      '#options' => [0 => $this->t('Disable'), 1 => $this->t('Enable')],
    ];

    $form['size_guide_modal_content'] = [
      '#type' => 'text_format',
      '#format' => $config->get('size_guide_modal_content.format'),
      '#title' => $this->t('Size guide content'),
      '#default_value' => $config->get('size_guide_modal_content.value'),
    ];

    return $form;
  }

}
