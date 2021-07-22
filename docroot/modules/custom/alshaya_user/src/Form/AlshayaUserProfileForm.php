<?php

namespace Drupal\alshaya_user\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\user\ProfileForm;

/**
 * Form controller for user forms (edit).
 */
class AlshayaUserProfileForm extends ProfileForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $account = $this->entity;
    $account->save();
    $form_state->setValue('uid', $account->id());

    $this->messenger()->addMessage($this->t('Contact details changes have been saved.'));
  }

}
