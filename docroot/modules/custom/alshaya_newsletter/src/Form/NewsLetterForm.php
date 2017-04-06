<?php

namespace Drupal\alshaya_newsletter\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class NewsLetterForm.
 */
class NewsLetterForm extends FormBase {

  /**
   * Post to link from block configuration.
   *
   * @var array
   */
  protected $postURL;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_newsletter_subscribe';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $post_url = NULL) {
    $this->postURL = $post_url;
    $site_name = $this->config('system.site')->get('name');
    $form['email'] = [
      '#type' => 'email',
      '#required' => TRUE,
      '#placeholder' => $this->t('Enter your email address'),
      '#prefix' => '<div class="newsletter-block-label">' . $this->t('Get email offers and the latest news from @site_name', ['@site_name' => $site_name]) . '</div>',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('submit'),
    ];

    $form['#action'] = isset($this->postURL['subscription_post_url']) ? $this->postURL['subscription_post_url'] : '';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Just implementation.
  }

}
