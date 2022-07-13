<?php

namespace Drupal\alshaya_performance\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Alshaya Performance Settings Form to store and alter configuration.
 */
class AlshayaPerformanceSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_performance_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_performance.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_performance.settings');
    $config->set('enable_css_cls', $form_state->getValue('enable_css_cls'));
    $config->save();
    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('alshaya_performance.settings');
    $form['enable_css_cls'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Improve CLS with CSS'),
      '#description' => $this->t('(EXPERIMENTAL) Provides better visual stability by adding temporary heights on drupal container markup, thereby reducing layout shifts when content is injected by JS'),
      '#default_value' => $config->get('enable_css_cls'),
    ];
    return $form;
  }

}
