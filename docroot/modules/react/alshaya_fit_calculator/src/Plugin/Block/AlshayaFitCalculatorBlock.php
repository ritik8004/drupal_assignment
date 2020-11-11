<?php

namespace Drupal\alshaya_fit_calculator\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides Fit calculator block.
 *
 * @Block(
 *   id = "alshaya_fit_calculator",
 *   admin_label = @Translation("Alshaya fit calculator")
 * )
 */
class AlshayaFitCalculatorBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['calculator_values'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Values'),
      '#description' => $this->t('Json array'),
      '#default_value' => isset($config['calculator_values']) ? $config['calculator_values'] : '',
    ];

    $form['size_conversion_html'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Size conversion chart html'),
      '#description' => $this->t('Html for modal to show size conversion chart'),
      '#default_value' => isset($config['size_conversion_html']) ? $config['size_conversion_html'] : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['calculator_values'] = $values['calculator_values'];
    $this->configuration['size_conversion_html'] = $values['size_conversion_html'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    return [
      '#markup' => '<div id="fit-calculator-container"></div>',
      '#attached' => [
        'library' => [
          'alshaya_fit_calculator/alshaya_fit_calculator',
        ],
        'drupalSettings' => [
          'fitCalculator' => [
            'sizes' => $config['calculator_values'] ?? NULL,
          ],
        ],
      ],
    ];
  }

}
