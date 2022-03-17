<?php

namespace Drupal\alshaya_rcs_bazaar_voice\Service;

use Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice;

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
      // @todo Add category overrides. Check getProductBazaarVoiceDetails().
      'hide_fields_write_review' => [],
      'myaccount_reviews_limit' => $config->get('myaccount_reviews_limit'),
    ];
    $settings['bazaar_voice'] = array_merge($settings['bazaar_voice'], $basic_configs);

    return $settings;
  }

  /**
   * {@inheritDoc}
   */
  public function getProductReviewStatistics(string $product_id) {
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

}
