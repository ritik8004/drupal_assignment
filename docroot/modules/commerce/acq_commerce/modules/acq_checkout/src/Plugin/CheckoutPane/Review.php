<?php

namespace Drupal\acq_checkout\Plugin\CheckoutPane;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;

/**
 * Provides the review pane.
 *
 * @ACQCheckoutPane(
 *   id = "review",
 *   label = @Translation("Review"),
 *   defaultStep = "review",
 * )
 */
class Review extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    /** @var \Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface[] $enabled_panes */
    $enabled_panes = array_filter($this->checkoutFlow->getPanes(), fn($pane) => $pane->getStepId() != '_disabled');

    foreach ($enabled_panes as $pane_id => $pane) {
      if (!$pane->isVisible()) {
        continue;
      }

      $config = $pane->getConfiguration();

      if ($summary = $pane->buildPaneSummary()) {
        $label = $pane->getLabel();
        $edit_link = Link::createFromRoute($this->t('Edit'), 'acq_checkout.form', [
          'step' => $pane->getStepId(),
        ]);
        $label .= ' (' . $edit_link->toString() . ')';

        $pane_form[$pane_id] = [
          '#type' => 'fieldset',
          '#title' => $label,
          '#weight' => $config['weight'],
        ];
        $pane_form[$pane_id]['summary'] = [
          '#markup' => $summary,
        ];
      }
    }

    return $pane_form;
  }

}
