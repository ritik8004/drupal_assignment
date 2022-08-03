<?php

namespace Drupal\alshaya_msclarity\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configuration form for homepage data.
 */
class AlshayaMsclarityConfigForm extends ConfigFormBase {
  /**
   * Config form settings.
   *
   * @var string
   */
  public const SETTINGS = 'alshaya_msclarity.settings';

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * File usage service.
   *
   * @var \Drupal\file\FileUsage\FileUsageInterface
   */
  protected $fileUsage;

  /**
   * Current user object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Construct configuration form for home page.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);
    $this->config = $config_factory->get(static::SETTINGS);
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
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_msclarity_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config(static::SETTINGS);
    $form['#tree'] = TRUE;
    $form['account'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('General settings'),
    ];
    $form['account']['msclarity_id'] = [
      '#title' => $this->t('Clarity ID'),
      '#type' => 'textfield',
      '#default_value' => $config->get('msclarity_id'),
      '#size' => 20,
      '#maxlength' => 20,
      '#required' => TRUE,
      '#description' => $this->t('Clarity ID'),
    ];

    // Visibilty settings.
    $form['advanced'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Visibility Settings'),
    ];
    $form['visibility']['page_vis_settings']['pages'] = [
      '#type' => 'details',
      '#title' => $this->t('Pages'),
      '#group' => 'advanced',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];

    $options = [
      $this->t('Every page except the listed pages'),
      $this->t('The listed pages only'),
    ];
    $form['visibility']['page_vis_settings']['pages']['msclarity_visibility_pages'] = [
      '#type' => 'radios',
      '#options' => $options,
      '#default_value' => $config->get('pages_vis_settings.msclarity_visibility_pages'),
      '#required' => TRUE,
      '#title' => $this->t('Add tracking to specific pages'),
    ];

    $form['visibility']['page_vis_settings']['pages']['msclarity_pages'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Pages'),
      '#title_display' => 'invisible',
      '#default_value' => $this->config->get('pages_vis_settings.msclarity_pages'),
      '#description' => $this->t('Specify pages by using their paths. Enter one path per line.'),
      '#rows' => 10,
    ];

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable(static::SETTINGS);
    $config->set('msclarity_id', $form_state->getValue('account', 'msclarity_id'))->save();
    $fields = $form_state->getValue(['visibility', 'page_vis_settings']);
    foreach ($fields as $key => $value) {
      if ($key == 'pages') {
        $config->set($key . '_vis_settings.msclarity_visibility_' . $key, $value['msclarity_visibility_' . $key])->save();
        $config->set($key . '_vis_settings.msclarity_' . $key, $value['msclarity_' . $key])->save();
      }
      elseif ($key == 'roles') {
        $config->set($key . '_vis_settings.msclarity_visibility_' . $key, $value['msclarity_visibility_' . $key])->save();
        $config->set($key . '_vis_settings.msclarity_' . $key, $value['msclarity_' . $key])->save();
      }
    }
    parent::submitForm($form, $form_state);
  }

}
