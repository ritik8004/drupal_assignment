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
    $wishlist_config = $this->config('alshaya_wishlist.settings');

    $form['alshaya_wishlist']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Wishlist'),
      '#description' => $this->t('Switch to enable or disable Wishlist feature.'),
      '#default_value' => $wishlist_config->get('enabled'),
    ];

    $form['alshaya_wishlist']['remove_after_addtocart'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Remove from wishlist'),
      '#description' => $this->t('If checked, product will be removed from the wishlist it has been added to cart from PDP, PLP, Wishlist or any other pages.'),
      '#default_value' => $wishlist_config->get('remove_after_addtocart'),
    ];

    $form['alshaya_wishlist']['wishlist_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Wishlist Label'),
      '#description' => $this->t('Label used for wishlist feature as per brand requirement.'),
      '#maxlength' => 255,
      '#default_value' => $wishlist_config->get('wishlist_label'),
    ];

    $form['alshaya_wishlist']['empty_wishlist_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Empty Wishlist Message'),
      '#description' => $this->t('Provides a static message that will be shown when the Wishlist is empty.'),
      '#maxlength' => 255,
      '#default_value' => $wishlist_config->get('empty_wishlist_message'),
    ];

    // Add the token tree UI.
    $form['alshaya_wishlist']['token_tree'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => ['alshaya_wishlist'],
      '#show_restricted' => TRUE,
      '#weight' => 90,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_wishlist.settings')
      ->set('enabled', $form_state->getValue('enabled'))
      ->set('remove_after_addtocart', $form_state->getValue('remove_after_addtocart'))
      ->set('empty_wishlist_message', $form_state->getValue('empty_wishlist_message'))
      ->set('wishlist_label', $form_state->getValue('wishlist_label'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
