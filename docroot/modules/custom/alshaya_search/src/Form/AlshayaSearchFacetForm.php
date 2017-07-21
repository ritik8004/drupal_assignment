<?php

namespace Drupal\alshaya_search\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facets\Form\FacetForm;

/**
 * Class AlshayaSearchFacetForm.
 *
 * @package Drupal\alshaya_search\Form
 */
class AlshayaSearchFacetForm extends FacetForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $facet = $this->entity;
    $form['third_party_settings']['alshaya_search_fieldset'] = [
      '#type' => 'details',
      '#title' => t('Alshaya search Facet Config'),
      '#open' => TRUE,
    ];

    $form['third_party_settings']['alshaya_search_fieldset']['display_textbox'] = [
      '#type' => 'checkbox',
      '#title' => t('Display textbox & allow users to narrow down the list of facet items.'),
      '#default_value' => $facet->getThirdPartySetting('alshaya_search', 'display_textbox'),
    ];

    $form['third_party_settings']['alshaya_search_fieldset']['display_textbox_item_count'] = [
      '#type' => 'textfield',
      '#title' => t('Display textbox if number of facet items exceed this.'),
      '#default_value' => $facet->getThirdPartySetting('alshaya_search', 'display_textbox_item_count'),
      '#states' => [
        'visible' => [
          ':input[name="third_party_settings[alshaya_search_fieldset][display_textbox]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $facet = $this->entity;
    $third_party_settings = $form_state->getValue('third_party_settings');
    $facet->setThirdPartySetting('alshaya_search', 'display_textbox', $third_party_settings['alshaya_search_fieldset']['display_textbox']);
    $facet->setThirdPartySetting('alshaya_search', 'display_textbox_item_count', $third_party_settings['alshaya_search_fieldset']['display_textbox_item_count']);
  }

}
