<?php

namespace Drupal\alshaya_wishlist\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Alshaya Wishlist configuration form.
 */
class AlshayaWishlistConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_wishlist_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_wishlist.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['alshaya_wishlist']['wishlist_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Wishlist'),
      '#description' => $this->t('Switch to enable or disable wishlist feature.'),
      '#default_value' => $this->config('alshaya_wishlist.settings')->get('wishlist_enabled'),
    ];

    $form['alshaya_wishlist']['empty_wishlist_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Empty Wishlist Message'),
      '#description' => $this->t('Provides a static message that will be shown when the wishlist is empty.'),
      '#maxlength' => 255,
      '#default_value' => $this->config('alshaya_wishlist.settings')->get('empty_wishlist_message'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_wishlist.settings')
      ->set('wishlist_enabled', $form_state->getValue('wishlist_enabled'))
      ->set('empty_wishlist_message', $form_state->getValue('empty_wishlist_message'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
