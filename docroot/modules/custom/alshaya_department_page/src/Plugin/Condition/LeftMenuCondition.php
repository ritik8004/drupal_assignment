<?php

namespace Drupal\alshaya_department_page\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the 'Left menu condition' condition.
 *
 * @Condition(
 *   id = "left_menu_condition",
 *   label = @Translation("Left menu block condition"),
 *   context_definitions = {
 *     "node" = @ContextDefinition(
 *        "entity:node",
 *        label = @Translation("node")
 *     )
 *   }
 * )
 */
class LeftMenuCondition extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * Creates a new LeftMenuCondition object.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *  phpcs:disable
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    // phpcs:enable
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['leftMenuActive'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Apply 'show left menu' condition on this block"),
      '#default_value' => $this->configuration['leftMenuActive'],
      '#description' => $this->t('If selected, this block will only be displayed on those department pages on which Show left menu field is checked.'),
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['leftMenuActive'] = $form_state->getValue('leftMenuActive');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['leftMenuActive' => 0] + parent::defaultConfiguration();
  }

  /**
   * Provides a human readable summary of the condition's configuration.
   */
  public function summary() {
    $status = $this->getContextValue('leftMenuActive') ? $this->t('enabled') : $this->t('disabled');
    return $this->t(
      'The node has left menu block @status.',
      ['@status' => $status]);
  }

  /**
   * Evaluates the condition and returns TRUE or FALSE accordingly.
   *
   * @return bool
   *   FALSE if the condition has been met, TRUE otherwise.
   */
  public function evaluate() {
    if (empty($this->configuration['leftMenuActive']) && !$this->isNegated()) {
      return TRUE;
    }

    $node = $this->getContextValue('node');
    if ($node->hasField('field_show_left_menu') && $node->get('field_show_left_menu')->value == 0) {
      return FALSE;
    }
    return TRUE;
  }

}
