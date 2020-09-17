<?php

namespace Drupal\alshaya_aura\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\alshaya_aura\Helper\AuraStatus;

/**
 * Provides a AURA Status Condition.
 *
 * @Condition(
 *   id = "aura_status_condition",
 *   label = @Translation("Aura Status Condition"),
 *   context_definitions = {
 *     "user" = @ContextDefinition("entity:user", label = @Translation("User"))
 *   }
 * )
 */
class AuraStatusCondition extends ConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['status'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('When the user has the following AURA status'),
      '#default_value' => $this->configuration['status'],
      '#options' => $this->getAllAuraStatus(),
      '#description' => $this->t('If you select no status, the condition will evaluate to TRUE for all users.'),
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'status' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['status'] = array_filter($form_state->getValue('status'));
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    if (empty($this->configuration['status']) && !$this->isNegated()) {
      return TRUE;
    }
    $user = $this->getContextValue('user');
    return (bool) array_intersect($this->configuration['status'], [$user->field_aura_loyalty_status->value]);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    $aura_statuses = array_intersect_key($this->getAllAuraStatus(), $this->configuration['status']);
    if (count($aura_statuses) > 1) {
      $aura_statuses = implode(', ', $aura_statuses);
    }
    else {
      $aura_statuses = reset($aura_statuses);
    }
    if (!empty($this->configuration['negate'])) {
      return $this->t('The user do not have AURA status in @aura_statuses', ['@aura_statuses' => $aura_statuses]);
    }
    else {
      return $this->t('The user has AURA status in @aura_statuses', ['@aura_statuses' => $aura_statuses]);
    }
  }

  /**
   * Get all AURA Status.
   */
  public function getAllAuraStatus() {
    $all_aura_status = [
      AuraStatus::APC_NOT_LINKED_NO_DATA => $this->t('Not Linked No Data'),
      AuraStatus::APC_NOT_LINKED_MDC_DATA => $this->t('Not Linked MDC Data'),
      AuraStatus::APC_LINKED_VERIFIED => $this->t('Linked Verified'),
      AuraStatus::APC_LINKED_NOT_VERIFIED => $this->t('Linked Not Verified'),
      AuraStatus::APC_NOT_LINKED_NOT_U => $this->t('Not Linked Not You'),
    ];

    return $all_aura_status;
  }

}
