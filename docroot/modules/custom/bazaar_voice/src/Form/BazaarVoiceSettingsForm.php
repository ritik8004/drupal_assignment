<?php

namespace Drupal\bazaar_voice\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Class BazaarVoice Settings Form.
 *
 * @package Drupal\bazaar_voice\Form
 */
class BazaarVoiceSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bazaar_voice_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['bazaar_voice.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('bazaar_voice.settings');

    $form['basic_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Basic settings'),
      '#open' => FALSE,
    ];

    $form['basic_settings']['api_base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Base Url'),
      '#description' => $this->t('BazaarVoice api base url for [stg] and [prod] enviroments.'),
      '#default_value' => $config->get('api_base_url'),
    ];

    $form['basic_settings']['conversations_apikey'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Conversations API Key'),
      '#description' => $this->t('API key is required to authenticate API user and check permission to access particular client data.  If you do not have an API key you can get one @url', [
        '@url' => Link::fromTextAndUrl($this->t('here'), Url::fromUri('https://portal.bazaarvoice.com/developer-tools'))
          ->toString(),
      ]),
      '#size' => 40,
      '#maxlength' => 255,
      '#default_value' => $config->get('conversations_apikey'),
    ];

    $form['basic_settings']['shared_secret_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Shared Secret Key'),
      '#description' => $this->t('BazaarVoice shared encoding key to create a user authentication string.'),
      '#default_value' => $config->get('shared_secret_key'),
    ];

    $form['basic_settings']['max_age'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Max age'),
      '#description' => $this->t('Max age to expire user authentication string. Please read the document for more info @url', [
        '@url' => Link::fromTextAndUrl($this->t('here'), Url::fromUri('https://developer.bazaarvoice.com/conversations-api/tutorials/submission/authentication/client-mastered'))
          ->toString(),
      ]),
      '#default_value' => $config->get('max_age'),
    ];

    $form['basic_settings']['api_version'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Version'),
      '#description' => $this->t('Which API version to use?'),
      '#default_value' => $config->get('api_version'),
    ];

    $form['basic_settings']['locale'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Locale'),
      '#default_value' => $config->get('locale'),
      '#description' => $this->t('Locale to display Labels, Configuration, Product Attributes and Category Attributes in. The default value is the locale defined in the display associated with the API key.'),
    ];

    $form['basic_settings']['content_locale'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Content Locale'),
      '#default_value' => $config->get('content_locale'),
      '#description' => $this->t('Locale of the content to display. If this filter is not defined, all content regardless of its locale is returned. To return specific content by locale, define the value in the filter. A wildcard character “*” can be used to define the value, e.g., “en*” returns all content in English (en_US, en_AE, en_KW, etc.) or you can use a single ContentLocale code (e.g., "en_KW"). ContentLocale codes are case-sensitive'),
    ];

    $form['basic_settings']['bvpixel_base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('BV Pixel Base URL'),
      '#description' => $this->t('Base URL for BV pixel script provided by BazaarVoice.'),
      '#default_value' => $config->get('bvpixel_base_url'),
    ];

    $form['basic_settings']['client_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Name'),
      '#default_value' => $config->get('client_name'),
      '#description' => $this->t('The client name provided by BazaarVoice.'),
    ];

    $form['basic_settings']['site_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Site Id'),
      '#default_value' => $config->get('site_id'),
      '#description' => $this->t('It is the value of deployment zone set in the Conversations configuration hub within the BazaarVoice Workbench. The default value is main_site. Please read the document for more info @url', [
        '@url' => Link::fromTextAndUrl($this->t('here'), Url::fromUri('https://developer.bazaarvoice.com/conversations-api/tutorials/bv-pixel/set-up'))
          ->toString(),
      ]),
    ];

    $form['basic_settings']['environment'] = [
      '#type' => 'textfield',
      '#title' => $this->t('environment'),
      '#default_value' => $config->get('environment'),
      '#description' => $this->t('The deployment environment of BazaarVoice where we implement BazaarVoice Pixel.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->configFactory()->getEditable('bazaar_voice.settings')
      ->set('api_base_url', $values['api_base_url'])
      ->set('conversations_apikey', $values['conversations_apikey'])
      ->set('shared_secret_key', $values['shared_secret_key'])
      ->set('max_age', $values['max_age'])
      ->set('api_version', $values['api_version'])
      ->set('locale', $values['locale'])
      ->set('content_locale', $values['content_locale'])
      ->set('bvpixel_base_url', $values['bvpixel_base_url'])
      ->set('client_name', $values['client_name'])
      ->set('site_id', $values['site_id'])
      ->set('environment', $values['environment'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
