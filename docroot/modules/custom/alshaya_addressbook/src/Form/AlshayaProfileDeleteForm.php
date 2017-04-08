<?php

namespace Drupal\alshaya_addressbook\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\profile\Form\ProfileDeleteForm;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Url;

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
  public function getQuestion() {
    // If not address book profile.
    if ($this->entity->getType() != 'address_book') {
      return parent::getQuestion();
    }

    return $this->t('Delete address');
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

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    if ($this->entity->getType() != 'address_book') {
      return $form;
    }

    $form['actions']['submit']['#ajax'] = [
      'callback' => '::deleteConfirmRedirect',
    ];

    $form['actions']['cancel']['#url'] = Url::fromRoute('alshaya_addressbook.close_modal');
    $form['actions']['cancel']['#attributes']['class'][] = 'use-ajax';
    $form['actions']['cancel']['#attributes']['data-dialog-type'] = 'modal';

    return $form;
  }

  /**
   * Submit callback for delete to close modal and reload the page.
   */
  public function deleteConfirmRedirect(array $form, FormStateInterface $form_state) {
    if ($form_state->isExecuted()) {
      $response = new AjaxResponse();
      $response->addCommand(new CloseModalDialogCommand());
      $response->addCommand(new RedirectCommand(Url::fromRoute('entity.profile.type.address_book.user_profile_form', [
        'user' => $this->entity->getOwnerId(),
        'profile_type' => 'address_book',
      ])->toString()));
      return $response;
    }
  }

  /**
   * Close modal window.
   */
  public function closeModal() {
    $response = new AjaxResponse();
    $response->addCommand(new CloseModalDialogCommand());
    return $response;
  }

}
