<?php

namespace Drupal\alshaya_xb\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\alshaya_xb\Service\DomainConfigOverrides;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides condition for block visibility based on market.
 *
 * @Condition(
 *   id = "alshaya_market_condition",
 *   label = @Translation("Alshaya Market Visibility"),
 * )
 */
class AlshayaMarketCondition extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Domain override service.
   *
   * @var \Drupal\alshaya_xb\Service\DomainConfigOverrides
   */
  protected $domainOverrides;

  /**
   * Creates a new AlshayaMarketCondition instance.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\alshaya_xb\Service\DomainConfigOverrides $domain_overrides
   *   Domain override service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    DomainConfigOverrides $domain_overrides,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->configFactory = $config_factory;
    $this->domainOverrides = $domain_overrides;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('alshaya_xb.domain_config_overrides'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // Get the list of available domains.
    $domain_list = $this->configFactory->get('alshaya_xb.settings')->get('domain_mapping');

    $form['domain_visibility_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable market based visibility check'),
      '#default_value' => $this->configuration['domain_visibility_enabled'],
    ];

    $options = [];
    // Get the list of all the available markets.
    foreach ($domain_list as $domain) {
      $options[$domain['code']] = $domain['country'];
    }

    $form['markets'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Select the Markets'),
      '#default_value' => $this->configuration['markets'],
      '#description' => $this->t('Select one or more markets where this block should appear. The block will only be visible on selected markets. The market is automatically detected based on the domain in the url.'),
      '#options' => $options,
      '#states' => [
        'visible' => [
          ':input[name="visibility[alshaya_market_condition][domain_visibility_enabled]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form += parent::buildConfigurationForm($form, $form_state);
    unset($form['negate']);
    unset($form['context_mapping']);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Save the submitted value to configuration.
    $this->configuration['domain_visibility_enabled'] = $form_state->getValue('domain_visibility_enabled');
    // Remove all the previous entries if the condition is disabled.
    if (!$this->configuration['domain_visibility_enabled']) {
      $this->configuration['markets'] = [];
    }
    else {
      $this->configuration['markets'] = $form_state->getValue('markets');
    }

    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    // The default configuration will be have the block hidden (0).
    return [
      'domain_visibility_enabled' => FALSE,
      'markets' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    if (!$this->configuration['domain_visibility_enabled']) {
      return TRUE;
    }

    $configOverrides = $this->domainOverrides->getConfigByDomain();
    if (empty($configOverrides) || empty($configOverrides['code'])) {
      return TRUE;
    }

    $markets = $this->configuration['markets'] ?? [];
    // If domain visibility is set & doesn't match with the current site then
    // return false.
    if (!$markets[$configOverrides['code']]) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cache_tags = parent::getCacheTags();

    return array_merge($cache_tags, $this->configFactory->get('alshaya_xb.settings')->getCacheTags() ?? []);
  }

}
