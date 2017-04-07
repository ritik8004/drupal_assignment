<?php

namespace Drupal\alshaya_addressbook\Form;

use Drupal\profile\Form\ProfileDeleteForm;

/**
 * Provides a confirmation form for deleting a profile entity.
 */
class AlshayaProfileDeleteForm extends ProfileDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('You have selected to delete this address, are you sure?');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('yes, delete this address');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelText() {
    return $this->t('No, take me back');
  }

  /**
   * {@inheritdoc}
   */
  protected function getDeletionMessage() {
    return $this->t('The address has been deleted.');
  }

}
