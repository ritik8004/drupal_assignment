<?php

namespace Drupal\alshaya_acm_product_category\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;

/**
 * Provides the 'Hide facet on PLP' condition.
 *
 * @Condition(
 *   id = "hide_facet_condition_plp",
 *   label = @Translation("Hide facet on PLP condition"),
 *   context = {
 *     "taxonomy_term" = @ContextDefinition(
 *        "entity:taxonomy_term",
 *        label = @Translation("taxonomy_term")
 *     )
 *   }
 * )
 */
class PLPHideFacetCondition extends ConditionPluginBase implements ContainerFactoryPluginInterface {

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
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['hideFacet'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Apply 'hide facet on PLP styles condition' on this block"),
      '#default_value' => $this->configuration['hideFacet'],
      '#description' => $this->t('If selected, this block will not be displayed on campaign-plp-style-1 plp pages.'),
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
   * Condition return FALSE if PLP layout is campaign-plp-style-1.
   *
   * This condition is used for PLP to hide the related facet block
   * when campaign-plp-style-1 layout is used.
   *
   * @return bool
   *   TRUE if the condition has been met, FALSE otherwise.
   */
  public function evaluate() {
    if (empty($this->configuration['hideFacet']) && !$this->isNegated()) {
      return TRUE;
    }

    $term = $this->getContextValue('taxonomy_term');
    if ($term->hasField('field_plp_layout') && $term->get('field_plp_layout')->value == ProductCategoryTree::PLP_LAYOUT_1) {
      return FALSE;
    }
    return TRUE;
  }

}
