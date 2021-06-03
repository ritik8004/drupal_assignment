<?php

namespace Drupal\alshaya_bazaar_voice\Service;

use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_commerce\SKUInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\Session\AccountProxy;
use Drupal\node\NodeInterface;
use Symfony\Component\Yaml\Yaml;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides integration with BazaarVoice.
 */
class AlshayaBazaarVoice {

  const ALSHAYA_BAZAARVOICE_FORM_ID = 'alshaya_bv_write_review';

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $connection;

  /**
   * Alshaya BazaarVoice API helper.
   *
   * @var \Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoiceApiHelper
   */
  protected $alshayaBazaarVoiceApiHelper;

  /**
   * The current user service object.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  public $currentUser;

  /**
   * Entity Repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * SKU Manager service object.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * SKU images manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $skuImagesManager;

  /**
   * Request stock service object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * BazaarVoiceApiWrapper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Database\Driver\mysql\Connection $connection
   *   Database service.
   * @param \Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoiceApiHelper $alshaya_bazaar_voice_api_helper
   *   Alshaya BazaarVoice API helper.
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   The current account object.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   Entity Repository service.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager service object.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU images manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              EntityTypeManagerInterface $entity_type_manager,
                              Connection $connection,
                              AlshayaBazaarVoiceApiHelper $alshaya_bazaar_voice_api_helper,
                              AccountProxy $current_user,
                              EntityRepositoryInterface $entityRepository,
                              SkuManager $sku_manager,
                              SkuImagesManager $sku_images_manager,
                              RequestStack $request_stack) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->connection = $connection;
    $this->alshayaBazaarVoiceApiHelper = $alshaya_bazaar_voice_api_helper;
    $this->currentUser = $current_user;
    $this->entityRepository = $entityRepository;
    $this->skuManager = $sku_manager;
    $this->skuImagesManager = $sku_images_manager;
    $this->currentRequest = $request_stack->getCurrentRequest();
  }

  /**
   * Get reviews data from BV api.
   *
   * @param array $skus
   *   batch of Skus.
   *
   * @return array|null
   *   BV attributes data to be indexed in algolia.
   */
  public function getDataFromBvReviewFeeds(array $skus) {
    $sanitized_sku = [];
    foreach ($skus as $sku) {
      $sanitized_sku[] = $this->skuManager->getSanitizedSku($sku);
    }
    $skus = implode(',', $sanitized_sku);
    $extra_params = [
      'filter' => 'id:' . $skus,
      'stats' => 'reviews',
    ];
    $request = $this->alshayaBazaarVoiceApiHelper->getBvUrl('data/products.json', $extra_params);
    $url = $request['url'];
    $request_options['query'] = $request['query'];

    $result = $this->alshayaBazaarVoiceApiHelper->doRequest('GET', $url, $request_options);
    if (!$result['HasErrors'] && isset($result['Results'])) {
      $response = [];
      foreach ($result['Results'] as $value) {
        $rating_distribution = $this->processRatingDistribution($value['ReviewStatistics']['RatingDistribution']);
        if ($value['ReviewStatistics']['TotalReviewCount'] > 0) {
          $response['ReviewStatistics'][$value['Id']] = [
            'AverageOverallRating' => $value['ReviewStatistics']['AverageOverallRating'],
            'TotalReviewCount' => $value['ReviewStatistics']['TotalReviewCount'],
            'RatingDistribution' => $rating_distribution['rating_distribution'],
            'RatingStars' => ['rating_' . round($value['ReviewStatistics']['AverageOverallRating'])],
          ];
        }
      }

      return $response;
    }

    return NULL;
  }

  /**
   * Helper function to fetch sku from node ids.
   *
   * @param array $nids
   *   Node ids.
   *
   * @return array
   *   Array of Sku Ids of the item.
   */
  public function getSkusByNodeIds(array $nids) {
    if (empty($nids)) {
      return [];
    }

    $query = $this->connection->select('node__field_skus', 'nfs')
      ->fields('nfs', ['field_skus_value'])
      ->distinct()
      ->condition('entity_id', $nids, 'IN');

    return $query->execute()->fetchAllKeyed(0, 0);
  }

  /**
   * Process ratings range for product.
   *
   * @param array $rating
   *   Rating range.
   *
   * @return array
   *   A rating range processed for algolia rating facet.
   */
  public function processRatingDistribution(array $rating) {
    if (empty($rating)) {
      return NULL;
    }

    usort($rating, function ($rating_value1, $rating_value2) {
      return $rating_value2['RatingValue'] <=> $rating_value1['RatingValue'];
    });

    $rating_range = [];
    // Rating stars and histogram data.
    foreach ($rating as $value) {
      $rating_range['rating_distribution'][] = 'rating_' . $value['RatingValue'] . '_' . $value['Count'];
    }

    return $rating_range;
  }

  /**
   * Get fields from BazaarVoice submission forms and sync in webform.
   *
   * @return array
   *   array|NULL.
   */
  public function syncFieldsFromBvSubmissionForm($product_id) {
    $extra_params = [
      'ProductId' => $product_id,
      'action' => '',
    ];
    $request = $this->alshayaBazaarVoiceApiHelper->getBvUrl('data/submitreview.json', $extra_params);
    $url = $request['url'];
    $request_options['query'] = $request['query'];

    $result = $this->alshayaBazaarVoiceApiHelper->doRequest('GET', $url, $request_options);

    if (!$result['HasErrors'] && isset($result['Data']['Fields'])) {
      return $this->updateAlshayaBvWriteReviewWebForm($result['Data']['Fields']);
    }

    return NULL;
  }

  /**
   * Process and update the BazaarVoice submission forms fields in webform.
   *
   * @return array
   *   Updated form fields in webform.
   */
  public function updateAlshayaBvWriteReviewWebForm($fields) {
    $is_new = FALSE;

    if ($fields) {
      // Load webform fields by form id.
      $webforms = $this->entityTypeManager->getStorage('webform')
        ->loadByProperties(['id' => self::ALSHAYA_BAZAARVOICE_FORM_ID]);
      $webform = reset($webforms);
      $form_fields = [];
      foreach ($fields as $key => $value) {
        if (strpos($key, 'photo') !== FALSE) {
          continue;
        }
        $key = strtolower(preg_replace("/[^A-Za-z0-9-]/", '_', $key));
        $field = '';
        switch ($value['Type']) {
          case 'BooleanInput':
            $field = [
              '#type' => 'checkbox',
              '#required' => $value['Required'],
              '#title' => $value['Label'],
              '#value' => $value['Value'],
              '#id' => $value['Id'],
              '#default_value' => $value['Default'],
              '#group_type' => 'boolean',
              '#visible' => FALSE,
            ];
            break;

          case 'TextAreaInput':
            $field = [
              '#type' => 'textarea',
              '#required' => $value['Required'],
              '#title' => $value['Label'],
              '#value' => $value['Value'],
              '#minlength' => $value['MinLength'],
              '#id' => $value['Id'],
              '#maxlength' => $value['MaxLength'],
              '#default_value' => $value['Default'],
              '#group_type' => 'textarea',
              '#visible' => FALSE,
            ];
            break;

          case 'SelectInput':
            $options = $this->processOptionsField($value['Options']);
            $field = [
              '#type' => 'select',
              '#title' => $value['Label'],
              '#required' => $value['Required'],
              '#default_value' => $options['default'],
              '#options' => $options['options'],
              '#id' => $value['Id'],
              '#group_type' => 'select',
              '#visible' => FALSE,
            ];
            break;

          default:
            $field = [
              '#type' => 'textfield',
              '#required' => $value['Required'],
              '#title' => $value['Label'],
              '#value' => $value['Value'],
              '#minlength' => $value['MinLength'],
              '#id' => $value['Id'],
              '#maxlength' => $value['MaxLength'],
              '#default_value' => $value['Default'],
              '#group_type' => 'textfield',
              '#visible' => FALSE,
            ];
        }
        // Ignore the fields exist already.
        if (!in_array($key, array_keys($webform->getElementsDecoded()))) {
          $form_fields[$key] = $field;
        }
      }
      // Add BazaarVoice fields in write a review webform.
      if (!empty($form_fields)) {
        $new_elements = array_merge($webform->getElementsDecoded(), $form_fields);
        $is_new = ($webform->setElements($new_elements)->save()) ? TRUE : FALSE;
      }
    }
    return $is_new;
  }

  /**
   * Get basic cofigurations defined for BazaarVoice.
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
    }
    $basic_configs['api_version'] = $config->get('api_version');
    $basic_configs['locale'] = $config->get('locale');
    $basic_configs['content_locale'] = $config->get('content_locale');
    $basic_configs['Include'] = $config->get('bv_content_types');
    $basic_configs['reviews_pagination_type'] = $config->get('reviews_pagination_type');
    $basic_configs['reviews_initial_load'] = $config->get('reviews_initial_load');
    $basic_configs['reviews_on_loadmore'] = $config->get('reviews_on_loadmore');
    $basic_configs['reviews_per_page'] = $config->get('reviews_per_page');
    $basic_configs['write_review_submission'] = $config->get('write_review_submission');
    $basic_configs['write_review_tnc'] = $config->get('write_review_tnc');
    $basic_configs['write_review_guidlines'] = $config->get('write_review_guidlines');
    $basic_configs['comment_form_tnc'] = $config->get('comment_form_tnc');
    $basic_configs['comment_box_min_length'] = $config->get('comment_box_min_length');
    $basic_configs['comment_box_max_length'] = $config->get('comment_box_max_length');
    $basic_configs['pdp_rating_reviews'] = $config->get('pdp_rating_reviews');
    $basic_configs['myaccount_rating_reviews'] = $config->get('myaccount_rating_reviews');
    $basic_configs['plp_rating_reviews'] = $config->get('plp_rating_reviews');

    return $basic_configs;
  }

  /**
   * Helper function to get the sorting options from configs.
   *
   * @return array
   *   Sorting options value.
   */
  public function getSortingOptions() {
    $available_options = [];

    $config = $this->configFactory->get('bazaar_voice_sort_review.settings');
    $sort_options = $config->get('sort_options');
    $sort_option_labels = $config->get('sort_options_labels');

    if (!empty($sort_options)) {
      $available_options[] = $sort_option_labels[0];
      foreach ($sort_options as $val) {
        foreach ($sort_option_labels as $k => $v) {
          $value = explode(':', $v['value']);
          if ($value[0] === $val) {
            $available_options[$k] = $sort_option_labels[$k];
          }
        }
      }
    }

    return array_values($available_options);
  }

  /**
   * Get the Filter options from configs.
   *
   * @return array
   *   Filter options.
   */
  public function getPdpFilterOptions() {
    $filter_options = $this->configFactory->get('bazaar_voice_filter_review.settings')->get('pdp_filter_options');
    if (!empty($filter_options)) {
      return Yaml::parse($filter_options);
    }

    return NULL;
  }

  /**
   * Get the BazaarVoice error messages from configs.
   *
   * @return array
   *   Error messages.
   */
  public function getBazaarVoiceErrorMessages() {
    $available_error_messages = [];

    $config = $this->configFactory->get('bazaar_voice_error_messages.settings');
    $error_messages = $config->get('error_messages');

    if (!empty($error_messages)) {
      foreach ($error_messages as $error) {
        $available_error_messages[$error['value']] = $error['label'];
      }
    }

    return $available_error_messages;
  }

  /**
   * Get category based conifgurations will be applied in write review form.
   *
   * @param \Drupal\node\NodeInterface $productNode
   *   Product node.
   *
   * @return array|null
   *   Write a review fields configs.
   */
  public function getCategoryBasedConfig(NodeInterface $productNode) {
    $config = $this->configFactory->get('bazaar_voice.settings');
    if ($config->get('pdp_rating_reviews')) {
      return NULL;
    }

    $showRatingReviews = TRUE;
    $hide_fields_write_review = [];
    $category = $productNode->get('field_category')->getValue();
    foreach ($category as $term) {
      $term_parents = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadAllParents($term['target_id']);
      foreach ($term_parents as $term_obj) {
        // Enable R&R feature based on category.
        if (!empty($term_obj->get('field_rating_review')->getValue()[0]['value'])) {
          $showRatingReviews = FALSE;
        }
        // Get fields from category not be displayed in write a review form.
        if (!empty($term_obj->get('field_write_review_form_fields')->getValue())) {
          $field_ids = [];
          foreach ($term_obj->get('field_write_review_form_fields')->getValue() as $value) {
            $field_ids[] = $value['value'];
          }
          $hide_fields_write_review = $field_ids;
        }
      }
    }

    return [
      'show_rating_reviews' => $showRatingReviews,
      'hide_fields_write_review' => $hide_fields_write_review,
    ];
  }

  /**
   * Process option values getting from BazaarVoice.
   *
   * @return array
   *   Formated options to be synced in BazaarVoice webform.
   */
  public function processOptionsField($options) {
    $formated_options = [];
    if ($options) {
      foreach ($options as $option) {
        if ($option['Selected']) {
          $formated_options['default'] = $option['Value'];
        }
        $formated_options['options'][$option['Value']] = $option['Label'];
      }
    }
    return $formated_options;
  }

  /**
   * Write a review fields configs created in webform.
   *
   * @return array
   *   Write a review fields configs.
   */
  public function getWriteReviewFieldsConfig() {
    $webforms = $this->entityTypeManager->getStorage('webform')
      ->loadByProperties(['id' => self::ALSHAYA_BAZAARVOICE_FORM_ID]);
    $webform = reset($webforms);
    // Get fields info with label translations.
    $field_configs = $this->entityRepository->getTranslationFromContext($webform);
    $elements = $field_configs->get('elements');
    $elements = Yaml::parse($elements);

    $translated_field_configs = [];
    foreach ($webform->getElementsDecoded() as $key => $value) {
      $translated_field_configs[$key] = $value;
      if (isset($elements[$key])) {
        $translated_field_configs[$key] = array_merge($value, $elements[$key]);
      }
    }

    return $translated_field_configs;
  }

  /**
   * Generate UAS token to use in write a review form for authenticated user.
   *
   * @return string
   *   Encoded UAS token.
   */
  public function generateEncodedUasToken() {
    $config = $this->configFactory->get('bazaar_voice.settings');
    $sharedKey = $config->get('shared_secret_key');
    $maxAge = $config->get('max_age');
    $userId = $this->currentUser->id();
    $mail = $this->currentUser->getEmail();

    // URL-encoded query string.
    $userStr = "date=" . urlencode(date('Ymd')) . "&userid=" . urlencode($userId) . "&EmailAddress=" . urlencode($mail) . "&maxage=" . urlencode($maxAge);
    // Encode the signature using HMAC SHA-256.
    $signature = hash_hmac('sha256', $userStr, $sharedKey);
    // Concatenate the signature and hex-encoded string of parameters.
    $uas = $signature . bin2hex($userStr);

    return $uas;
  }

  /**
   * Get all the strings as key value pair from utility.
   *
   * @return array
   *   returns all strings related to bazaarvoice feature.
   */
  public function getBazaarvoiceStrings() {
    \Drupal::moduleHandler()->loadInclude('alshaya_bazaar_voice', 'inc', 'alshaya_bazaar_voice.static_strings');
    $strings = [
      '#theme' => 'alshaya_strings',
      '#strings' => _alshaya_bazaar_voice_static_strings(),
    ];
    return $strings;
  }

  /**
   * Get attached libary and settings for bazaarvoice myaccount.
   *
   * @param array $settings
   *   Current product info .
   *
   * @return array
   *   Return to the build array with attached libraries.
   */
  public function getOrdersPageAttachment(array $settings) {
    $attached = [
      'library' => [
        'alshaya_bazaar_voice/myorders',
        'alshaya_bazaar_voice/iovation',
      ],
      'drupalSettings' => [
        'productInfo' => $settings,
      ],
    ];
    return $attached;
  }

  /**
   * Fetch product details for my account page.
   *
   * @param string $sku_id
   *   Sku id of the product.
   * @param array $basic_configs
   *   Basic configurations of bazaarvoice.
   *
   * @return array
   *   Details for all the products.
   */
  public function getMyAccountProductSettings($sku_id, array $basic_configs) {
    $productNode = $this->skuManager->getDisplayNode($sku_id);
    $productArray = [];
    if ($productNode instanceof NodeInterface) {
      $productArray['alshaya_bazaar_voice'] = $this->getProductBazaarVoiceDetails($sku_id, $productNode, $basic_configs);
      // Add current user details.
      $productArray['user_details'] = $this->getCurrentUserDetails($productNode);
    }
    return $productArray;
  }

  /**
   * Fetch drupal settings for individual product.
   *
   * @param mixed $sku
   *   SKU text or full entity object.
   * @param \Drupal\node\NodeInterface $productNode
   *   Product node.
   * @param array $basic_configs
   *   Basic configurations of bazaarvoice.
   *
   * @return array|null
   *   Drupal settings with product details.
   */
  public function getProductBazaarVoiceDetails($sku, NodeInterface $productNode, array $basic_configs) {
    $sku = $sku instanceof SKUInterface ? $sku : SKU::loadFromSku($sku);
    // Disable BazaarVoice Rating and Review in PDP
    // if checkbox is checked for any categories or its Parent Categories.
    $category_based_config = $this->getCategoryBasedConfig($productNode);
    if (empty($category_based_config) || !$category_based_config['show_rating_reviews']) {
      return;
    }
    /** @var \Drupal\alshaya_acm_product\SkuImagesManager $skuImagesManager */
    $skuImagesManager = \Drupal::service('alshaya_acm_product.sku_images_manager');
    $media = $skuImagesManager->getFirstImage($sku, 'pdp');
    $image_url = '';
    if (!empty($media)) {
      $image_url = file_create_url($media['drupal_uri']);
    }

    // Get avalable sorting options from config.
    $sorting_options = $this->getSortingOptions();
    // Get avalable BazaarVoice error messages from config.
    $bv_error_messages = $this->getBazaarVoiceErrorMessages();
    // Get the filter options to be rendered on review summary.
    $filter_options = $this->getPdpFilterOptions();

    // Get country code.
    $country_code = _alshaya_custom_get_site_level_country_code();

    $settings = [
      'product' => [
        'url' => $productNode->toUrl()->toString(),
        'title' => $productNode->label(),
        'image_url' => $image_url,
      ],
      'bazaar_voice' => [
        'stats' => 'Reviews',
        'sorting_options' => $sorting_options,
        'filter_options' => $filter_options,
        'country_code' => $country_code,
        'error_messages' => $bv_error_messages,
      ],
      'base_url' => \Drupal::request()->getSchemeAndHttpHost(),
      'bv_auth_token' => \Drupal::request()->get('bv_authtoken'),
      'hide_fields_write_review' => $category_based_config['hide_fields_write_review'],
    ];
    $settings['bazaar_voice'] = array_merge($settings['bazaar_voice'], $basic_configs);

    return $settings;
  }

  /**
   * Fetch drupal settings for user details.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Product node.
   *
   * @return array|null
   *   Drupal settings with product details.
   */
  public function getCurrentUserDetails(NodeInterface $node) {
    if (is_object($node) && $node instanceof NodeInterface) {
      /** @var \Drupal\alshaya_acm_product\SkuManager $skuManager */
      $skuManager = \Drupal::service('alshaya_acm_product.skumanager');
      $sku = $skuManager->getSkuForNode($node);
      // Get sanitized sku.
      $sanitized_sku = $skuManager->getSanitizedSku($sku);
      $productReviewData = $this->getProductInfoForCurrentUser($sanitized_sku);
      $settings = [
        'user' => [
          'email' => $this->currentUser->getEmail(),
          'id' => $this->currentUser->id(),
          'name' => $this->currentUser->getUsername(),
          'review' => $productReviewData,
        ],
      ];
      return $settings;
    }
  }

  /**
   * Get product info reviewed by current user.
   *
   * @param string $productId
   *   product Id.
   *
   * @return array|null
   *   returns product review status and rating.
   */
  public function getProductInfoForCurrentUser($productId) {
    $userId = $this->currentUser->id();
    $extra_params = [
      'filter' => 'id:' . $userId,
      'Include' => 'Reviews,Products',
      'stats' => 'Reviews',
    ];
    $request = $this->alshayaBazaarVoiceApiHelper->getBvUrl('data/authors.json', $extra_params);
    if (isset($request['url']) && isset($request['query'])) {
      $url = $request['url'];
      $request_options['query'] = $request['query'];
      $result = $this->alshayaBazaarVoiceApiHelper->doRequest('GET', $url, $request_options);
      if (!$result['HasErrors'] && isset($result['Includes'])) {
        if (isset($result['Includes']['Reviews'])) {
          foreach ($result['Includes']['Reviews'] as $review) {
            if ($review['ProductId'] === $productId) {
              $data = [
                'review_summary' => $review,
                'product_summary' => $result['Includes']['Products'][$productId],
                'user_rating' => $review['Rating'],
              ];
              return $data;
            }
          }
        }
      }
    }
    return NULL;
  }

}
