<?php

namespace Drupal\alshaya_addressbook\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Json;
use Drupal\profile\Form\ProfileForm;
use Drupal\Core\Url;

/**
 * Form controller for profile forms (add/edit).
 */
class AlshayaProfileForm extends ProfileForm {

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);

    if ($this->entity->getType() == 'address_book') {
      $user_id = $this->currentUser()->id();
      $element['cancel_button'] = [
        '#type' => 'link',
        '#title' => t('Cancel'),
        '#attributes' => [
          'class' => ['cancel-button', 'button'],
        ],
        '#weight' => 20,
        '#url' => Url::fromRoute('entity.profile.type.address_book.user_profile_form', [
          'user' => $user_id,
          'profile_type' => 'address_book',
        ]),
      ];

      // Open delete form in modal on address edit screen.
      $element['delete']['#attributes']['class'][] = 'use-ajax';
      $element['delete']['#attributes']['data-dialog-type'] = 'modal';
      $element['delete']['#attributes']['data-dialog-options'] = Json::encode(['width' => '341']);
      // Get current language.
      $current_language = \Drupal::languageManager()->getCurrentLanguage();
      if (isset($element['delete']['#url'])) {
        $element['delete']['#url']->setOption('language', $current_language);
      }

      // Render the active profiles.
      $element['active_profiles'] = [
        '#type' => 'view',
        '#name' => 'profiles',
        '#display_id' => 'profile_type_listing',
        '#arguments' => [$this->currentUser()->id(), 'address_book', 1],
        '#embed' => TRUE,
        '#title' => $this->t('Active @type', ['@type' => 'Address Book']),
        '#weight' => 30,
        '#pre_render' => [
          ['\Drupal\views\Element\View', 'preRenderViewElement'],
          'profile_views_add_title_pre_render',
        ],
      ];

      $element['delete']['#access'] = FALSE;
    }

    return $element;
  }

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
