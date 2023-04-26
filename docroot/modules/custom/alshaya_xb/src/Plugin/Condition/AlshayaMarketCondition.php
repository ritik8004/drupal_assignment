<?php

namespace Drupal\alshaya_xb\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
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
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Creates a new AlshayaMarketCondition instance.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\alshaya_xb\Service\DomainConfigOverrides $domain_overrides
   *   Domain override service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    AccountProxyInterface $currentUser,
    ConfigFactoryInterface $config_factory,
    DomainConfigOverrides $domain_overrides,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->currentUser = $currentUser;
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
      $container->get('current_user'),
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

    $form['domain_visibility'] = [
      '#title' => $this->t('Select the Markets'),
      '#type' => 'fieldset',
      '#description' => $this->t('Select from the list of available market,
        Based on this the visibility of this block varies. Leave empty to show
        this in all the above mentioned markets.'),
    ];

    foreach ($domain_list as $domain) {
      $form['domain_visibility'][$domain['code']] = [
        '#type' => 'checkbox',
        '#title' => $domain['country'],
        '#default_value' => $this->configuration['domain_visibility'][$domain['code']] ?? '',
      ];
    }

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
    $this->configuration['domain_visibility'] = $form_state->getValue('domain_visibility');

    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    // The default configuration will be have the block hidden (0).
    return [
      'domain_visibility' => [],
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
    if (empty($this->configuration['domain_visibility'])) {
      return TRUE;
    }

    $configOverrides = $this->domainOverrides->getConfigByDomain();
    if (empty($configOverrides) || empty($configOverrides['code'])) {
      return TRUE;
    }

    $domain_visibility = array_filter($this->configuration['domain_visibility'] ?? []);

    // If domain visibility is set & doesn't match with the current site then
    // return false.
    if (!empty($domain_visibility)
      && !array_key_exists($configOverrides['code'], $domain_visibility)) {
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
