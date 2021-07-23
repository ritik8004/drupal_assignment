<?php

namespace Drupal\acq_sku\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SKU Type Form.
 *
 * @package Drupal\acq_sku\Form
 */
class SKUTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $sku_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $sku_type->label(),
      '#description' => $this->t("Label for the SKU type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $sku_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\acq_sku\Entity\SKUType::load',
      ],
      '#disabled' => !$sku_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $sku_type = $this->entity;
    $status = $sku_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label SKU type.', [
          '%label' => $sku_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label SKU type.', [
          '%label' => $sku_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($sku_type->toUrl('collection'));
  }

}
