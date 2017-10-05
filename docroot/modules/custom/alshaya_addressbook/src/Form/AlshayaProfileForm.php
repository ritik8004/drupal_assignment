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

    if ($this->entity->bundle() == 'address_book') {
      $user_id = $this->currentUser()->id();

      if (\Drupal::request()->get('from') == 'checkout') {
        $element['cancel_button'] = [
          '#type' => 'link',
          '#title' => t('Cancel'),
          '#attributes' => [
            'class' => ['cancel-button', 'button'],
          ],
          '#weight' => 20,
          '#url' => Url::fromRoute('acq_checkout.form', [
            'step' => 'delivery',
          ]),
        ];
      }
      else {
        $element['cancel_button'] = [
          '#type' => 'link',
          '#title' => t('Cancel'),
          '#attributes' => [
            'class' => ['cancel-button', 'button'],
          ],
          '#weight' => 20,
          '#url' => Url::fromRoute('entity.profile.type.user_profile_form', [
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

        $element['delete']['#access'] = FALSE;
      }

    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $profile_type = $this->entity->bundle();

    // If address book profile.
    if ($profile_type == 'address_book') {
      // Update addressbook for user in Magento.
      /** @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager */
      $address_book_manager = \Drupal::service('alshaya_addressbook.manager');

      if ($address_book_manager->pushUserAddressToApi($entity)) {
        if ($this->entity->isNew()) {
          drupal_set_message($this->t('Address is added successfully.'));
        }
        else {
          drupal_set_message($this->t('Address is updated successfully.'));
        }
      }

      $form_state->setRedirect('entity.profile.type.user_profile_form', [
        'user' => $this->entity->getOwnerId(),
        'profile_type' => 'address_book',
      ]);
    }
    else {
      parent::save($form, $form_state);
    }

  }

}
