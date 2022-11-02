<?php

namespace Drupal\alshaya_xb\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Global-e Settings Form.
 */
class GlobaleSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'globale_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_xb.settings'];
  }

  /**
   * GlobaleSettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_xb.settings');

    $form['globale_js_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Global-e JS URL'),
      '#description' => $this->t('Enter the Global-e JS URL.'),
      '#required' => TRUE,
      '#default_value' => $config->get('globale_js_url'),
    ];

    $form['globale_css_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Global-e CSS URL'),
      '#description' => $this->t('Enter the Global-e CSS URL.'),
      '#required' => TRUE,
      '#default_value' => $config->get('globale_css_url'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_xb.settings');
    $config->set('globale_js_url', $form_state->getValue('globale_js_url'));
    $config->set('globale_css_url', $form_state->getValue('globale_css_url'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

}
