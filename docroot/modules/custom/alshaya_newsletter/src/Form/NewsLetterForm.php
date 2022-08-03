<?php

namespace Drupal\alshaya_newsletter\Form;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;

/**
 * Class News Letter Form.
 */
class NewsLetterForm extends FormBase {

  /**
   * The api wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_newsletter_subscribe';
  }

  /**
   * Class constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   The api wrapper.
   */
  public function __construct(AlshayaApiWrapper $api_wrapper) {
    $this->apiWrapper = $api_wrapper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_api.api')
    );
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
      '#type' => 'submit',
      '#value' => $this->t('sign up'),
      '#ajax' => [
        'event' => 'click',
        'callback' => '::submitFormAjax',
        'wrapper' => 'footer-newsletter-form-wrapper',
      ],
      '#suffix' => '<div id="footer-newsletter-form-wrapper"></div>',
      '#attributes' => [
        'class' => ['edit-newsletter', 'cv-validate-before-ajax'],
        'data-twig-suggestion' => 'newsletter',
        'data-style' => 'zoom-in',
      ],
    ];

    $form['#attached']['library'][] = 'alshaya_newsletter/newsletter_js';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitFormAjax(array &$form, FormStateInterface $form_state) {
    $data = [];
    if (!$form_state->hasAnyErrors() && !empty($form_state->getValue('email'))) {
      try {
        $subscription = $this->apiWrapper->subscribeNewsletter($form_state->getValue('email'));

        $status = is_array($subscription) ? $subscription['status'] : $subscription;

        if ($status) {
          $message = '<span class="message success">' . $this->t('Thank you for your subscription.') . '</span>';
          $data['message'] = 'success';
        }
        else {
          $message = '<span class="message error">' . $this->t('This email address is already subscribed.') . '</span>';
          $data['message'] = 'failure';
        }
      }
      catch (\Exception $e) {
        if (acq_commerce_is_exception_api_down_exception($e)) {
          $message = '<span class="message error">' . $e->getMessage() . '</span>';
        }
        else {
          $message = '<span class="message error">' . $this->t('Something went wrong, please try again later.') . '</span>';
        }

        $data['message'] = 'failure';
      }

      $data['html'] = '<div class="subscription-status">' . $message . '</div>';
    }
    else {
      $data['message'] = 'failure';
      $data['html'] = '<div class="subscription-status"><span class="message error">' . $this->t('Please enter an email address') . '</span></div>';
    }

    // Get the interval we want to show the message for on our ladda button.
    $interval = $this->config('alshaya_master.settings')->get('ajax_spinner_message_interval');
    $data['interval'] = $interval;

    // Prepare the ajax Response.
    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand(NULL, 'newsletterHandleResponse', [$data]));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
