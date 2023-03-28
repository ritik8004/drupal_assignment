<?php

namespace Drupal\alshaya_rcs_bazaar_voice\Service;

use Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice;
use Drupal\Core\Site\Settings;

/**
 * Integrates RCS with AlshayaBazaarVoice.
 */
class AlshayaRcsBazaarVoice extends AlshayaBazaarVoice {

  /**
   * {@inheritDoc}
   */
  public function getProductBazaarVoiceDetails($sku, array $basic_configs, array $item = []) {
    $settings = [];

    // Get available sorting options from config.
    $sorting_options = $this->getSortingOptions();
    // Get available BazaarVoice error messages from config.
    $bv_error_messages = $this->getBazaarVoiceErrorMessages();
    // Get the filter options to be rendered on review summary.
    $filter_options = $this->getPdpFilterOptions();

    // Get country code.
    $country_code = _alshaya_custom_get_site_level_country_code();

    $config = $this->configFactory->get('bazaar_voice.settings');

    $settings = [
      'bazaar_voice' => [
        'stats' => 'Reviews',
        'sorting_options' => $sorting_options,
        'filter_options' => $filter_options,
        'country_code' => $country_code,
        'error_messages' => $bv_error_messages,
      ],
      'base_url' => $this->currentRequest->getSchemeAndHttpHost(),
      'bv_auth_token' => $this->currentRequest->get('bv_authtoken'),
      'customer_id' => alshaya_acm_customer_is_customer($this->currentUser, TRUE),
      'myaccount_reviews_limit' => $config->get('myaccount_reviews_limit'),
    ];
    $settings['bazaar_voice'] = array_merge($settings['bazaar_voice'], $basic_configs);

    return $settings;
  }

  /**
   * Get product reviews URL.
   *
   * @param string $product_id
   *   Product ID.
   *
   * @return array|null
   *   Product reviews URL.
   */
  public function getProductReviewSchema(string $product_id) {
    static $response = [];
    $config = $this->configFactory->get('bazaar_voice.settings');
    $pdp_reviews_seo_limit = $config->get('pdp_reviews_seo_limit');
    if (isset($response[$product_id]) && !empty($response[$product_id])) {
      return $response[$product_id];
    }
    $extra_params = [
      'filter' => 'id:' . $product_id,
      'stats' => 'reviews',
      'include' => 'Reviews',
      'sort_reviews' => 'submissiontime:desc',
      'Limit_Reviews' => $pdp_reviews_seo_limit,
    ];
    $request = $this->alshayaBazaarVoiceApiHelper->getBvUrl('data/products.json', $extra_params);
    return $request;
  }

  /**
   * Returns settings for Bazaar voice.
   *
   * @return array
   *   Array of Bazaar voice settings
   */
  public function getRcsBazaarVoiceSettings() {
    $basic_configs = $this->getBasicConfigurations();
    $settings = $this->getProductBazaarVoiceDetails(NULL, $basic_configs);
    return $settings;
  }

  /**
   * Get the fields for BV product query.
   *
   * @return array
   *   Fields for BV product query.
   */
  public function getBvProductQueryFields() {
    $fields = [
      'total_count',
      'items' => [
        'type_id',
        'sku',
        'name',
        'url_key',
        'media_gallery' => [
          '... on ProductImage' => [
            'url',
            'label',
            'styles',
          ],
        ],
        'categories' => [
          'write_review_form_fields',
        ],
        '... on ConfigurableProduct' => [
          'variants' => [
            'product' => [
              'sku',
              'name',
            ],
          ],
        ],
      ],
    ];

    $this->moduleHandler->alter('alshaya_rcs_product_bv_product_fields', $fields);
    return $fields;
  }

  /**
   * Get basic configurations defined for bazaar voice.
   *
   * @param string $context
   *   Context.
   *
   * @return array
   *   BazaarVoice basic configurations.
   */
  public function getBasicConfigurations($context = 'web') {
    $basic_configs = [];
    $config = $this->configFactory->get('bazaar_voice.settings');
    if ($context === 'web') {
      $basic_configs['endpoint'] = $config->get('api_base_url');
      $basic_configs['passkey'] = $config->get('conversations_apikey');
      $basic_configs['max_age'] = $config->get('max_age');
      // Get Configs for Google translation API.
      $google_translations_api = Settings::get('google_translations_api');
      $basic_configs['google_api_endpoint'] = $google_translations_api['endpoint'] ?? '';
      $basic_configs['google_api_key'] = $google_translations_api['api_key'] ?? '';
      $basic_configs['pdp_rating_reviews'] = $config->get('pdp_rating_reviews');
      $basic_configs['plp_rating_reviews'] = $config->get('plp_rating_reviews');
      $basic_configs['myaccount_rating_reviews'] = $config->get('myaccount_rating_reviews');
      $basic_configs['bazaarvoice_settings_expiry'] = $this->configFactory->get('alshaya_rcs_bazaar_voice.settings')
        ->get('alshaya_rcs_bazaarvoice_settings_expiry');
    }
    return $basic_configs;
  }

  /**
   * {@inheritDoc}
   */
  public function getWriteReviewFieldsConfig() {
    // For V3 we return an empty array since we get the configs from the
    // MDC graphql API call.
    return [];
  }

}
