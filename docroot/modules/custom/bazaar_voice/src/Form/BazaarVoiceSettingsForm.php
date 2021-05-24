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

    $form['basic_settings']['bv_content_types'] = [
      '#type' => 'textfield',
      '#title' => $this->t('BV Content Types'),
      '#default_value' => $config->get('bv_content_types'),
      '#description' => $this->t('List of content types of BV associated with reviews API.'),
    ];

    $form['basic_settings']['environment'] = [
      '#type' => 'textfield',
      '#title' => $this->t('environment'),
      '#default_value' => $config->get('environment'),
      '#description' => $this->t('The deployment environment of BazaarVoice where we implement BazaarVoice Pixel.'),
    ];

    $form['basic_settings']['pdp_rating_reviews'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable ratings and reviews in PDP.'),
      '#default_value' => $config->get('pdp_rating_reviews'),
      '#description' => $this->t('This option should be checked to disable the ratings and reviews in PDP.'),
    ];

    $form['basic_settings']['write_review_submission'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Closed submission for unauthorized user.'),
      '#default_value' => $config->get('write_review_submission'),
      '#description' => $this->t('This option should be checked to enable closed submission for unauthorized user on the site.'),
    ];

    $form['basic_settings']['write_review_tnc'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Write a review T&C url'),
      '#default_value' => $config->get('write_review_tnc'),
      '#description' => $this->t('URL of Write Review Terms and Conditions. URL format should be an alias e.g write-review-terms-conditions.'),
    ];

    $form['basic_settings']['write_review_guidlines'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Write a review guidelines url'),
      '#default_value' => $config->get('write_review_guidlines'),
      '#description' => $this->t('URL of Write Review Guidelines. URL format should be an alias e.g write-review-guidelines.'),
    ];

    $form['basic_settings']['comment_form_tnc'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Comments T&C url'),
      '#default_value' => $config->get('comment_form_tnc'),
      '#description' => $this->t('URL of Comment Form Terms and Conditions. URL format should be an alias e.g comments-terms-conditions.'),
    ];

    $form['basic_settings']['comment_box_min_length'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Comment minimum character length'),
      '#default_value' => $config->get('comment_box_min_length'),
      '#description' => $this->t('Enter minimum character length for comment box text in comment form.'),
    ];

    $form['basic_settings']['comment_box_max_length'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Comment maximum character length'),
      '#default_value' => $config->get('comment_box_max_length'),
      '#description' => $this->t('Enter maximum character length for comment box text in comment form.'),
    ];

    $form['basic_settings']['bv_routes_list'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Routes for BazaarVoice analytics'),
      '#default_value' => $config->get('bv_routes_list'),
      '#description' => $this->t('Specify routes by using their route name which will be tracked by BazaarVoice for analytics. BV pixel script will be loaded only for the routes in this list. Enter one route per line.'),
    ];

    $form['basic_settings']['reviews_pagination_type'] = [
      '#type' => 'radios',
      '#weight' => 1,
      '#title' => $this->t('Choose either load more or pagination for reviews'),
      '#description' => $this->t('Specify if you wish to show all the reviews with load more or pagination options respectively.'),
      '#options' => [
        'load_more' => $this->t('Load more'),
        'pagination' => $this->t('Pagination'),
      ],
      '#default_value' => $config->get('reviews_pagination_type'),
    ];
    $form['basic_settings']['reviews_pagination_type']['reviews_initial_load'] = [
      '#type' => 'textfield',
      '#weight' => 2,
      '#title' => $this->t('Number of reviews on initial load'),
      '#default_value' => $config->get('reviews_initial_load'),
      '#description' => $this->t('Load specific number of reviews on initial load of PDP and my account.'),
      '#states' => [
        'visible' => [
          'input[name="reviews_pagination_type"]' => [
            'value' => 'load_more',
          ],
        ],
      ],
    ];
    $form['basic_settings']['reviews_pagination_type']['reviews_on_loadmore'] = [
      '#type' => 'textfield',
      '#weight' => 3,
      '#title' => $this->t('Number of reviews on load more click'),
      '#default_value' => $config->get('reviews_on_loadmore'),
      '#description' => $this->t('Load specific number of reviews on click of load more button on PDP and my account.'),
      '#states' => [
        'visible' => [
          'input[name="reviews_pagination_type"]' => [
            'value' => 'load_more',
          ],
        ],
      ],
    ];
    $form['basic_settings']['reviews_pagination_type']['reviews_per_page'] = [
      '#type' => 'textfield',
      '#weight' => 4,
      '#title' => $this->t('Number of reviews per page'),
      '#default_value' => $config->get('reviews_per_page'),
      '#description' => $this->t('Display a specific number of reviews per page on PDP and my account.'),
      '#states' => [
        'visible' => [
          'input[name="reviews_pagination_type"]' => [
            'value' => 'pagination',
          ],
        ],
      ],
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
      ->set('pdp_rating_reviews', $values['pdp_rating_reviews'])
      ->set('write_review_submission', $values['write_review_submission'])
      ->set('write_review_tnc', $values['write_review_tnc'])
      ->set('write_review_guidlines', $values['write_review_guidlines'])
      ->set('comment_form_tnc', $values['comment_form_tnc'])
      ->set('bv_content_types', $values['bv_content_types'])
      ->set('comment_box_min_length', $values['comment_box_min_length'])
      ->set('comment_box_max_length', $values['comment_box_max_length'])
      ->set('bv_routes_list', $values['bv_routes_list'])
      ->set('reviews_pagination_type', $values['reviews_pagination_type'])
      ->set('reviews_initial_load', $values['reviews_initial_load'])
      ->set('reviews_on_loadmore', $values['reviews_on_loadmore'])
      ->set('reviews_per_page', $values['reviews_per_page'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
