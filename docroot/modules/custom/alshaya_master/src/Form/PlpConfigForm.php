<?php

namespace Drupal\alshaya_master\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class PlpConfigForm.
 */
class PlpConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_plp_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_master.plp_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_master.plp_settings');

    // Autoplay on/off feature.
    $form['plp_video_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('PLP Video Settings'),
      '#open' => TRUE,
    ];

    $form['plp_video_settings']['enable_autoplay'] = [
      '#type' => 'checkbox',
      '#default_value' => $config->get('enable_autoplay'),
      '#title' => $this->t('Enable autoplay on videos.'),
    ];

    // PLP styles for which 'Hide Facet Block on PLP' condition should be used.
    $form['plp_hide_facet_styles'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('PLP Hide Facet Settings'),
      '#open' => TRUE,
    ];

    // PLP styles.
    $plp_style_list = $config->get('all_plp_layouts');
    $form['plp_hide_facet_styles']['facet_plp_styles'] = [
      '#type' => 'select',
      '#default_value' => $config->get('facet_plp_styles'),
      '#title' => $this->t('PLP Styles for Hide Facet Condition.'),
      '#description' => $this->t('Select the plp styles for which you want to use the "Hide Facet on PLP" condition should be used.'),
      '#options' => $plp_style_list,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_master.plp_settings');
    $config->set('enable_autoplay', $form_state->getValue('enable_autoplay'));
    $config->set('facet_plp_styles', $form_state->getValue('facet_plp_styles'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

}
