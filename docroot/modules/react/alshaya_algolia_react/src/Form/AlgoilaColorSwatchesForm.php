<?php

namespace Drupal\alshaya_algolia_react\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Alshaya Algolia Color Swatches settings.
 */
class AlgoilaColorSwatchesForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'algolia_color_swatches_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'alshaya_algolia_react.color_swatches',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_algolia_react.color_swatches');

    $form['plp_swatch_config'] = [
      '#type' => 'details',
      '#title' => $this->t('PLP/SLP Swatch config settings'),
      '#tree' => FALSE,
      '#open' => TRUE,
    ];
    $form['plp_swatch_config']['enable_plp_color_swatch_slider'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable / Disable Swatch Slider'),
      '#options' => [1 => $this->t('Enabled'), 0 => $this->t('Disabled')],
      '#default_value' => $config->get('enable_plp_color_swatch_slider') === FALSE ? 0 : 1,
    ];
    $form['plp_swatch_config']['swatch_type'] = [
      '#type' => 'select',
      '#options' => [
        'circle' => $this->t('Circle'),
        'square' => $this->t('Square'),
      ],
      '#title' => $this->t('Select Swatch Type'),
      '#default_value' => $config->get('swatch_type'),
    ];
    $form['plp_swatch_config']['no_of_swatches_plp'] = [
      '#type' => 'number',
      '#min' => '1',
      '#max' => '6',
      '#step' => '1',
      '#title' => $this->t('Set the swatch limit for PLP view'),
      '#default_value' => $config->get('no_of_swatches_plp'),
      '#description' => $this->t('Max number swatches to display upfront in PLP followed by carousel.'),
    ];
    $form['plp_swatch_config']['no_of_swatches_plp_mobile'] = [
      '#type' => 'number',
      '#min' => '1',
      '#max' => '4',
      '#step' => '1',
      '#title' => $this->t('Set the swatch limit for PLP on mobile view'),
      '#default_value' => $config->get('no_of_swatches_plp_mobile'),
      '#description' => $this->t('Max number swatches to display upfront in PLP on mobile view followed by carousel.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('alshaya_algolia_react.color_swatches')
      ->set('enable_plp_color_swatch_slider', $form_state->getValue('enable_plp_color_swatch_slider'))
      ->set('swatch_type', $form_state->getValue('swatch_type'))
      ->set('no_of_swatches_plp', (int) $form_state->getValue('no_of_swatches_plp'))
      ->set('no_of_swatches_plp_mobile', (int) $form_state->getValue('no_of_swatches_plp_mobile'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
