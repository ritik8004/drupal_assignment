<?php

namespace Drupal\alshaya_security\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * AlshayaSecurityConfigForm Class.
 */
class AlshayaSecurityConfigForm extends ConfigFormBase {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * Constructs a AlshayaSecurityConfigForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, DateFormatter $date_formatter) {
    parent::__construct($config_factory);
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_security.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_security_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_security.settings');

    $options = [0, 300, 31536000, 63072000, 94608000];

    $form['max_age'] = [
      '#type' => 'select',
      '#title' => $this->t('Max age'),
      '#description' => $this->t('The maximum age value for the header. See the <a href="https://tools.ietf.org/html/rfc6797">Strict Transport Security Definition</a> for more information.'),
      '#options' => array_map([
        $this->dateFormatter,
        'formatInterval',
      ], array_combine($options, $options)),
      '#default_value' => $config->get('max_age'),
    ];

    $form['subdomains'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include subdomains'),
      '#description' => $this->t('Whether to include the subdomains as part of the HSTS implementation.'),
      '#default_value' => $config->get('subdomains'),
    ];

    $form['preload'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include preload'),
      '#description' => $this->t('Whether to include the preload as part of the HSTS implementation.'),
      '#default_value' => $config->get('preload'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_security.settings')
      ->set('max_age', $form_state->getValue('max_age'))
      ->set('subdomains', $form_state->getValue('subdomains'))
      ->set('preload', $form_state->getValue('preload'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!is_numeric($form_state->getValue('max_age')) || $form_state->getValue('max_age') < 0) {
      $form_state->setErrorByName('max_age', $this->t('Value is not a number or out of bounds.'));
    }

    parent::validateForm($form, $form_state);
  }

}
