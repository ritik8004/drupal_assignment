<?php

namespace Drupal\acq_checkout\Plugin\CheckoutPane;

use Drupal\address\LabelHelper;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the address checkout pane.
 */
class AddressFormBase extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $address = $form_state->getTemporaryValue('address');
    $country = $address->country_id ?? 'US';
    $countryRepository = \Drupal::service('address.country_repository');
    $subdivisionRepository = \Drupal::service('address.subdivision_repository');
    $addressFormatRepository = \Drupal::service('address.address_format_repository');
    $address_format = $addressFormatRepository->get($country);
    $labels = LabelHelper::getFieldLabels($address_format);

    $form_state->setTemporaryValue('pane_id', $this->getId());

    $pane_form['address'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['address_wrapper'],
      ],
    ];
    $pane_form['address']['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#default_value' => $address->firstname ?? '',
      '#required' => TRUE,
      '#placeholder' => $this->t('First Name*'),
    ];
    $pane_form['address']['last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#default_value' => $address->lastname ?? '',
      '#required' => TRUE,
      '#placeholder' => $this->t('Last Name*'),
    ];
    $pane_form['address']['street'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Address Line 1'),
      '#default_value' => $address->street ?? '',
      '#required' => TRUE,
      '#placeholder' => $this->t('Address Line 1*'),
    ];
    $pane_form['address']['telephone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Telephone'),
      '#default_value' => $address->telephone ?? '',
      '#required' => TRUE,
      '#placeholder' => $this->t('Telephone'),
    ];
    $pane_form['address']['street2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Address Line 2'),
      '#default_value' => $address->street2 ?? '',
      '#required' => FALSE,
      '#placeholder' => $this->t('Address Line 2'),
    ];
    $pane_form['address']['dynamic_parts'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['dynamic_parts'],
      ],
    ];
    $pane_form['address']['dynamic_parts']['city'] = [
      '#type' => 'textfield',
      '#title' => $labels['locality'],
      '#default_value' => $address->city ?? '',
      '#required' => TRUE,
      '#placeholder' => $labels['locality'],
    ];
    $pane_form['address']['dynamic_parts']['region'] = [
      '#type' => 'select',
      '#title' => $labels['administrativeArea'],
      '#options' => $subdivisionRepository->getList([$country]),
      '#empty_option' => '- ' . $labels['administrativeArea'] . ' -',
      '#required' => TRUE,
      '#validated' => TRUE,
      '#default_value' => $address->region ?? '',
    ];
    $pane_form['address']['dynamic_parts']['postcode'] = [
      '#type' => 'textfield',
      '#title' => $labels['postalCode'],
      '#default_value' => $address->postcode ?? '',
      '#required' => TRUE,
      '#placeholder' => $labels['postalCode'],
    ];
    $pane_form['address']['dynamic_parts']['country_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Country'),
      '#options' => $countryRepository->getList(),
      '#default_value' => $country,
      '#required' => TRUE,
      '#ajax' => [
        'callback' => $this->addressAjaxCallback(...),
        'wrapper' => 'dynamic_parts',
      ],
    ];

    return $pane_form;
  }

  /**
   * Ajax handler for country selector.
   */
  public static function addressAjaxCallback($form, FormStateInterface $form_state) {
    $pane_id = $form_state->getTemporaryValue('pane_id');
    $dynamic_parts =& $form[$pane_id]['address']['dynamic_parts'];
    $values = $form_state->getValue($form['#parents']);
    $country = $values[$pane_id]['address']['dynamic_parts']['country_id'];
    $addressFormatRepository = \Drupal::service('address.address_format_repository');
    $address_format = $addressFormatRepository->get($country);
    $subdivisionRepository = \Drupal::service('address.subdivision_repository');
    $options = $subdivisionRepository->getList([$country]);
    $labels = LabelHelper::getFieldLabels($address_format);

    // Update region options based on country.
    $dynamic_parts['region']['#options'] = $options;
    $dynamic_parts['region']['#required'] = TRUE;
    $dynamic_parts['region']['#access'] = TRUE;

    // Update labels.
    $cityLabel = $labels['locality'];
    $postcodeLabel = $labels['postalCode'];
    $regionLabel = $labels['administrativeArea'];
    $dynamic_parts['region']['#title'] = $regionLabel;
    $dynamic_parts['postcode']['#title'] = $postcodeLabel;
    $dynamic_parts['city']['#title'] = $cityLabel;

    if (empty($cityLabel)) {
      $dynamic_parts['city']['#required'] = FALSE;
      $dynamic_parts['city']['#access'] = FALSE;
    }

    if (empty($postcodeLabel)) {
      $dynamic_parts['postcode']['#required'] = FALSE;
      $dynamic_parts['postcode']['#access'] = FALSE;
    }

    if (empty($regionLabel) || empty($options)) {
      $dynamic_parts['region']['#required'] = FALSE;
      $dynamic_parts['region']['#access'] = FALSE;
    }

    return $dynamic_parts;
  }

}
