<?php

namespace Drupal\rcs_placeholders\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\StackedRouteMatchInterface;
use Drupal\rcs_placeholders\Service\RcsPhPathProcessor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides condition for "RCS" feature.
 *
 * @Condition(
 *   id = "rcs_context",
 *   label = @Translation("RCS pages"),
 * )
 */
class RcsContext extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The master route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * RCS Context constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\StackedRouteMatchInterface $route_match
   *   The current route match.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, StackedRouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match->getMasterRouteMatch();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['use_rcs_context'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use RCS Context.'),
      '#default_value' => $this->configuration['use_rcs_context'],
      '#description' => $this->t('This block will be displayed on RCS Content.'),
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['use_rcs_context'] = $form_state->getValue('use_rcs_context');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'use_rcs_context' => FALSE,
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
    if (empty($this->configuration['use_rcs_context'])) {
      return TRUE;
    }

    return RcsPhPathProcessor::isRcsPage();
  }

}
