<?php

namespace Drupal\alshaya_addressbook\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\profile\Form\ProfileForm;

/**
 * Form controller for profile forms (add/edit).
 */
class AlshayaProfileForm extends ProfileForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $profile_type = $this->entity->getType();

    // If address book profile.
    if ($profile_type == 'address_book') {
      switch ($this->entity->save()) {
        case SAVED_NEW:
          drupal_set_message($this->t('Address is added successfully.'));
          break;

        case SAVED_UPDATED:
          drupal_set_message($this->t('Address is updated successfully.'));
          break;
      }

      $user_id = $this->currentUser()->id();
      $form_state->setRedirect('entity.profile.type.address_book.user_profile_form', [
        'user' => $user_id,
        'profile_type' => 'address_book',
      ]);
    }
    else {
      parent::save($form, $form_state);
    }

  }

}
