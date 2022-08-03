<?php

namespace Drupal\alshaya_paragraphs\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Paragraphs Custom Config Form.
 */
class ParagraphsCustomConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_paragraphs_paragraphs_custom_config';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_paragraphs.paragraphs_custom_config'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_paragraphs.paragraphs_custom_config');

    $form['promo_block_display'] = [
      '#type' => 'select',
      '#title' => $this->t('Promo block display'),
      '#options' => [
        1 => $this->t('Image and button is clickable'),
        2 => $this->t('Only button is clickable'),
      ],
      '#default_value' => $config->get('promo_block_display') ?: '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_paragraphs.paragraphs_custom_config');
    $config->set('promo_block_display', $form_state->getValue('promo_block_display'));
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
