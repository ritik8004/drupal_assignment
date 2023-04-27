<?php

namespace Drupal\alshaya_algolia_react\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Alshaya Algolia Swipe Image settings.
 */
class AlgoliaSwipeImageForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'algolia_swipe_image_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'alshaya_algolia_react.swipe_image',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_algolia_react.swipe_image');
    $form['swipe_image_config'] = [
      '#type' => 'details',
      '#title' => $this->t('SLP/PLP Swipe Image config settings'),
      '#tree' => FALSE,
      '#open' => TRUE,
    ];
    $form['swipe_image_config']['enable_swipe_image_mobile'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable / Disable Swipe Image on Mobile view'),
      '#default_value' => $config->get('enable_swipe_image_mobile'),
      '#description' => $this->t('Enable / Disable Swipe Image only for Mobile View'),
    ];
    $form['swipe_image_config']['no_of_image_scroll'] = [
      '#type' => 'number',
      '#min' => '1',
      '#max' => '6',
      '#step' => '1',
      '#title' => $this->t('Set the swipe image limit for SRP/PLP view'),
      '#default_value' => $config->get('no_of_image_scroll'),
      '#description' => $this->t('Max number swipe image to display upfront in SRP/PLP followed by carousel.'),
    ];
    $form['swipe_image_config']['slide_effect'] = [
      '#type' => 'select',
      '#title' => $this->t('Set the swipe image slide effect'),
      '#options' => ['slide' => $this->t('Slide'), 'fade' => $this->t('Fade')],
      '#default_value' => $config->get('slide_effect'),
    ];
    $form['swipe_image_config']['image_slide_timing'] = [
      '#type' => 'number',
      '#min' => '0',
      '#max' => '2',
      '#step' => '0.1',
      '#title' => $this->t('Auto scroll for the Product Image'),
      '#description' => $this->t('Auto scroll timer(in sec) for auto scrolling the product images on listing pages.'),
      '#default_value' => $config->get('image_slide_timing') ?? 2,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('alshaya_algolia_react.swipe_image')
      ->set('enable_swipe_image_mobile', $form_state->getValue('enable_swipe_image_mobile'))
      ->set('no_of_image_scroll', (int) $form_state->getValue('no_of_image_scroll'))
      ->set('slide_effect', $form_state->getValue('slide_effect'))
      ->set('image_slide_timing', $form_state->getValue('image_slide_timing'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
