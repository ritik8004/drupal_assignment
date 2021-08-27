<?php

namespace Drupal\rcs_placeholders\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\StackedRouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides condition for "RCS" feature.
 *
 * @Condition(
 *   id = "rcs_entity",
 *   label = @Translation("RCS pages"),
 * )
 */
class RcsEntity extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The master route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * RcsEntity constructor.
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
    $form['rcs_nodes_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('RCS nodes.'),
      '#default_value' => $this->configuration['rcs_nodes_enabled'],
      '#description' => $this->t('This block will be displayed on RCS Product Content.'),
    ];
    $form['rcs_terms_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('RCS terms.'),
      '#default_value' => $this->configuration['rcs_terms_enabled'],
      '#description' => $this->t('This block will be displayed on RCS Category Terms.'),
    ];
    unset($form['negate']);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['rcs_nodes_enabled'] = $form_state->getValue('rcs_nodes_enabled');
    $this->configuration['rcs_terms_enabled'] = $form_state->getValue('rcs_terms_enabled');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'rcs_nodes_enabled' => FALSE,
      'rcs_terms_enabled' => FALSE,
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
    if ($this->configuration['rcs_terms_enabled'] &&
      $this->routeMatch->getRouteName() == 'entity.taxonomy_term.canonical') {
      $term = $this->routeMatch->getParameter('taxonomy_term');
      if ($term->bundle() === 'rcs_category') {
        return TRUE;
      }
    }
    if ($this->configuration['rcs_nodes_enabled'] &&
      $this->routeMatch->getRouteName() == 'entity.node.canonical') {
      $term = $this->routeMatch->getParameter('node');
      if ($term->bundle() === 'rcs_product') {
        return TRUE;
      }
    }
  }

}
