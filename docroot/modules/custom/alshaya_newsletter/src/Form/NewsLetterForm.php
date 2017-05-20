<?php

namespace Drupal\alshaya_newsletter\Form;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
      '#type' => 'email',
      '#required' => TRUE,
      '#placeholder' => $this->t('Enter your email address'),
      '#prefix' => '<div class="newsletter-block-label">' . $this->t('get email offers and the latest news from @site_name', ['@site_name' => $site_name]) . '</div>',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    try {
      $response = $this->apiWrapper->subscribeNewsletter($form_state->getValue('email'));
      // @TODO: Check if want to display a different message to users who
      // are already subscribed.
      // Status 0 means user is already subscribed.
      // If user is subscribed now, it will return 1.
      if ($response['status'] === 0) {
        drupal_set_message($this->t('Thank you for signing up to receive our emails.'), 'success');
      }
      else {
        drupal_set_message($this->t('Thank you for signing up to receive our emails.'), 'success');
      }
    }
    catch (\Exception $e) {
      drupal_set_message($this->t('Something went wrong, please try again later.'), 'warning');
    }
  }

}
