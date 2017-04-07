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
    // If not address book profile.
    if ($this->entity->getType() != 'address_book') {
      return parent::getDescription();
    }

    return $this->t('You have selected to delete this address, are you sure?');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    // If not address book profile.
    if ($this->entity->getType() != 'address_book') {
      return parent::getDescription();
    }

    return $this->t('yes, delete this address');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelText() {
    // If not address book profile.
    if ($this->entity->getType() != 'address_book') {
      return parent::getDescription();
    }

    return $this->t('No, take me back');
  }

  /**
   * {@inheritdoc}
   */
  protected function getDeletionMessage() {
    // If not address book profile.
    if ($this->entity->getType() != 'address_book') {
      return parent::getDescription();
    }

    return $this->t('Address is deleted successfully.');
  }

}
