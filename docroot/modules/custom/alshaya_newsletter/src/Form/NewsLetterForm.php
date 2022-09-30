<?php

namespace Drupal\alshaya_newsletter\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class News Letter Form.
 */
class NewsLetterForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_newsletter_subscribe';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $site_name = $this->config('system.site')->get('name');

    // We don't add token for this form, it will never be user specific.
    $form['#token'] = FALSE;

    // We set the action to empty string, it will always use AJAX anyways.
    $form['#action'] = '';

    $form['email'] = [
      '#title' => $this->t('Email address'),
      '#title_display' => 'invisible',
      '#type' => 'email',
      '#placeholder' => $this->t('Enter your email address'),
      '#prefix' => '<div class="newsletter-block-label">' . $this->t('get email offers and the latest news from @site_name', ['@site_name' => $site_name]) . '</div>',
      '#element_validate' => ['alshaya_valid_email_address'],
    ];

    $form['newsletter'] = [
      '#type' => 'button',
      '#value' => $this->t('sign up'),
      '#suffix' => '<div id="footer-newsletter-form-wrapper"></div>',
      '#attributes' => [
        'class' => ['edit-newsletter'],
        'data-twig-suggestion' => 'newsletter',
        'data-style' => 'zoom-in',
      ],
    ];

    $form['#attached']['library'][] = 'alshaya_newsletter/newsletter_js';
    $form['#attached']['drupalSettings']['newsletter']['apiUrl'] = '/V1/newsletter/subscription';
    $form['#attached']['drupalSettings']['newsletter']['ajaxSpinnerMessageInterval'] = $this->config('alshaya_master.settings')->get('ajax_spinner_message_interval');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
