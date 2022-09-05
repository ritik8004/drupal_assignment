<?php

namespace Drupal\alshaya_newsletter\Form;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\alshaya_spc\Helper\AlshayaSpcHelper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Site\Settings;

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
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Alshaya SPC Version Helper.
   *
   * @var \Drupal\alshaya_spc\Helper\AlshayaSpcHelper
   */
  protected $spcHelper;

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
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\alshaya_spc\Helper\AlshayaSpcHelper $spc_helper
   *   Alshaya SPC Version Helper.
   */
  public function __construct(
    AlshayaApiWrapper $api_wrapper,
    LanguageManagerInterface $language_manager,
    AlshayaSpcHelper $spc_helper) {
    $this->apiWrapper = $api_wrapper;
    $this->languageManager = $language_manager;
    $this->spcHelper = $spc_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_api.api'),
      $container->get('language_manager'),
      $container->get('alshaya_spc.helper')
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
    $form['#attached']['drupalSettings']['newsletter']['apiUrl'] = $this->getMagentoApiInfo();
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitFormAjax(array &$form, FormStateInterface $form_state) {
    $data = [];
    if (!$form_state->hasAnyErrors() && !empty($form_state->getValue('email'))) {
      $data['email'] = $form_state->getValue('email');
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
    $response->addCommand(new InvokeCommand(NULL, 'newsletterCallApi', [$data]));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * Gets the information around magento API.
   *
   * @return string
   *   The API link of Magento.
   */
  private function getMagentoApiInfo() {
    $cart_version = $this->spcHelper->getCommerceBackendVersion();
    if ($cart_version != 2) {
      return '';
    }
    $api_url = '';
    $api_settings = Settings::get('alshaya_api.settings');
    $store_settings = Settings::get('magento_lang_prefix');
    $current_language = $this->languageManager->getCurrentLanguage()->getId();

    // We proxy the requests via cloudflare, so we use the current domain as is
    // without any language suffix so HTTP_HOST is enough.
    $api_url = 'https://' . $_SERVER['HTTP_HOST'];

    // Use proxy on only specific environments.
    if (Settings::get('alshaya_use_proxy', FALSE)) {
      $api_url = '/proxy/?url=' . $api_settings['magento_host'];
    }

    // Add the prefix for rest and store code.
    $api_url .= '/rest/' . $store_settings[$current_language];

    return $api_url;
  }

}
