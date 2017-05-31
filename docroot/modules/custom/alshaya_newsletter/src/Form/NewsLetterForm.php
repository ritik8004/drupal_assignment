<?php

namespace Drupal\alshaya_newsletter\Form;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;

/**
 * Class NewsLetterForm.
 */
class NewsLetterForm extends FormBase {

  /**
   * The api wrapper.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
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
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   The api wrapper.
   */
  public function __construct(APIWrapper $api_wrapper) {
    $this->apiWrapper = $api_wrapper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acq_commerce.api')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $site_name = $this->config('system.site')->get('name');

    $form['email'] = [
      '#title' => $this->t('Email address'),
      '#title_display' => 'invisible',
      '#type' => 'email',
      '#required' => TRUE,
      '#placeholder' => $this->t('Enter your email address'),
      '#prefix' => '<div class="newsletter-block-label">' . $this->t('get email offers and the latest news from @site_name', ['@site_name' => $site_name]) . '</div>',
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
        'class' => ['edit-newsletter'],
        'data-twig-suggestion' => 'newsletter',
      ],
    ];

    $form['#attached']['library'][] = 'alshaya_newsletter/newsletter_js';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitFormAjax(array &$form, FormStateInterface $form_state) {
    if (!$form_state->hasAnyErrors()) {
      try {
        $subscription = $this->apiWrapper->subscribeNewsletter($form_state->getValue('email'));

        if ($subscription['status'] === 0) {
          $message = '<span class="message error">' . $this->t('This email address is already subscribed.') . '</span>';
        }
        else {
          $message = '<span class="message success">' . $this->t('Thank you for your subscription.') . '</span>';
        }
      }
      catch (\Exception $e) {
        $message = '<span class="message error">' . $this->t('Something went wrong, please try again later.') . '</span>';
      }

      $html = '<div class="subscription-status">' . $message . '</div>';
    }
    else {
      $html = '<div class="subscription-status"><span class="message error">' . $this->t('Please enter an email address') . '</span></div>';
    }

    // Prepare the ajax Response.
    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('#footer-newsletter-form-wrapper', $html));
    $response->addCommand(new InvokeCommand(NULL, 'stopNewsletterSpinner'));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
