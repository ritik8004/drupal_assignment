<?php

namespace Drupal\alshaya_olapic\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Configure Alshaya Olapic Config settings.
 */
class OlapicConfigForm extends ConfigFormBase {

  /**
   * The language manger service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(LanguageManagerInterface $language_manager, ConfigFactoryInterface $config_factory) {
    $this->languageManager = $language_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('language_manager'),
      $container->get('config.factory')
     );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'olapic_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'alshaya_olapic.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_olapic.settings');
    $lang = $this->languageManager->getCurrentLanguage()->getId();
    $data_api_field_name = 'olapic_' . $lang . '_data_apikey';
    $form['development_mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Development mode'),
      '#default_value' => $config->get('development_mode') ?? 0,
      '#options' => [0 => $this->t('No'), 1 => $this->t('Yes')],
    ];
    $form[$data_api_field_name] = [
      '#title' => $this->t('Olapic Data Apikey'),
      '#type' => 'textfield',
      '#default_value' => $config->get($data_api_field_name) ?? '',
      '#size' => 100,
    ];
    $form['olapic_external_script_url'] = [
      '#title' => $this->t('Olapic External Script Url'),
      '#type' => 'textfield',
      '#default_value' => $config->get('olapic_external_script_url') ?? '',
      '#size' => 100,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $lang = $this->languageManager->getCurrentLanguage()->getId();
    $data_api_field_name = 'olapic_' . $lang . '_data_apikey';
    $this->configFactory->getEditable('alshaya_olapic.settings')
      ->set('development_mode', $form_state->getValue('development_mode'))
      ->set($data_api_field_name, $form_state->getValue($data_api_field_name))
      ->set('olapic_external_script_url', $form_state->getValue('olapic_external_script_url'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
