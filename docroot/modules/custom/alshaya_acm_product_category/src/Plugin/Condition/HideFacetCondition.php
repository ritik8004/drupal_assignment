<?php

namespace Drupal\alshaya_acm_product_category\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the 'Hide facet condition' condition.
 *
 * @Condition(
 *   id = "hide_facet_condition",
 *   label = @Translation("Hide facet condition"),
 *   context = {
 *     "taxonomy_term" = @ContextDefinition(
 *        "entity:taxonomy_term",
 *        label = @Translation("taxonomy_term")
 *     )
 *   }
 * )
 */
class HideFacetCondition extends ConditionPluginBase implements ContainerFactoryPluginInterface {

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
   * Creates a new HideFacetCondition object.
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
   *  @codingStandardsIgnoreStart
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    // @codingStandardsIgnoreEnd
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['hideFacet'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Apply 'hide facet condition' condition on this block"),
      '#default_value' => $this->configuration['hideFacet'],
      '#description' => $this->t('If selected, this block will not be displayed on moschino plp pages.'),
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['hideFacet'] = $form_state->getValue('hideFacet');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['hideFacet' => 0] + parent::defaultConfiguration();
  }

  /**
   * Provides a human readable summary of the condition's configuration.
   */
  public function summary() {
    $status = $this->getContextValue('hideFacet') ? $this->t('enabled') : $this->t('disabled');
    return $this->t(
      'The term has hide facet menu block @status.',
      ['@status' => $status]);
  }

  /**
   * Evaluates the condition and returns TRUE or FALSE accordingly.
   *
   * @return bool
   *   TRUE if the condition has been met, FALSE otherwise.
   */
  public function evaluate() {
    if (empty($this->configuration['hideFacet']) && !$this->isNegated()) {
      return TRUE;
    }

    $term = $this->getContextValue('taxonomy_term');
    if ($term->hasField('field_plp_style') && $term->get('field_plp_style')->value == 'moschino') {
      return FALSE;
    }
    return TRUE;
  }

}
