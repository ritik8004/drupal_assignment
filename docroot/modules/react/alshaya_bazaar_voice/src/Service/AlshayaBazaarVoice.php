<?php

namespace Drupal\alshaya_bazaar_voice\Service;

use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_commerce\SKUInterface;
use Drupal\alshaya_acm_product\SkuImagesHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mysql\Driver\Database\mysql\Connection;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\node\NodeInterface;
use Symfony\Component\Yaml\Yaml;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\File\FileSystemInterface;

/**
 * Provides integration with BazaarVoice.
 */
class AlshayaBazaarVoice {

  public const ALSHAYA_BAZAARVOICE_FORM_ID = 'alshaya_bv_write_review';

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
   * @var \Drupal\mysql\Driver\Database\mysql\Connection
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
   * Module Handler service object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * File system object.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * BazaarVoiceApiWrapper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\mysql\Driver\Database\mysql\Connection $connection
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
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module Handler service object.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The filesystem service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              EntityTypeManagerInterface $entity_type_manager,
                              Connection $connection,
                              AlshayaBazaarVoiceApiHelper $alshaya_bazaar_voice_api_helper,
                              AccountProxy $current_user,
                              EntityRepositoryInterface $entityRepository,
                              SkuManager $sku_manager,
                              SkuImagesManager $sku_images_manager,
                              RequestStack $request_stack,
                              ModuleHandlerInterface $moduleHandler,
                              FileSystemInterface $file_system,
                              LoggerChannelFactoryInterface $logger_factory) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->connection = $connection;
    $this->alshayaBazaarVoiceApiHelper = $alshaya_bazaar_voice_api_helper;
    $this->currentUser = $current_user;
    $this->entityRepository = $entityRepository;
    $this->skuManager = $sku_manager;
    $this->skuImagesManager = $sku_images_manager;
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->moduleHandler = $moduleHandler;
    $this->fileSystem = $file_system;
    $this->logger = $logger_factory->get('alshaya_bazaar_voice');
  }

  /**
   * Get reviews data from BV api.
   *
   * @param array $skus
   *   batch of Skus.
   * @param int $limit
   *   Limit the result response.
   *
   * @return array|null
   *   BV attributes data to be indexed in algolia.
   */
  public function getDataFromBvReviewFeeds(array $skus, $limit) {
    $request_options = [];
    $config = $this->configFactory->get('bazaar_voice.settings');
    $featured_reviews_limit = $config->get('featured_reviews_limit');
    $locations = $this->getPdpFilterOptions();

    $sanitized_sku = [];
    foreach ($skus as $sku) {
      $sanitized_sku[] = $this->skuManager->getSanitizedSku($sku);
    }
    $skus = implode(',', $sanitized_sku);
    $extra_params = [
      'filter' => 'id:' . $skus,
      'stats' => 'reviews',
      'limit' => $limit,
    ];
    // Check if limit is set to get featured reviews.
    if (!empty($featured_reviews_limit)) {
      $featured_reviews_params = [
        'include' => 'Reviews',
        'sort_reviews' => 'IsFeatured:desc',
        'Filter_reviews' => 'IsFeatured:True',
        'Limit_Reviews' => $featured_reviews_limit,
      ];
      $extra_params = array_merge($extra_params, $featured_reviews_params);
    }

    $request = $this->alshayaBazaarVoiceApiHelper->getBvUrl('data/products.json', $extra_params);
    $url = $request['url'];
    $request_options['query'] = $request['query'];

    $result = $this->alshayaBazaarVoiceApiHelper->doRequest('GET', $url, $request_options);
    if (!empty($result) && !$result['HasErrors'] && !empty($result['Results'])) {
      $response = [];
      foreach ($result['Results'] as $value) {
        if ($value['ReviewStatistics']['TotalReviewCount'] > 0) {
          // Distributed rating info.
          $rating_distribution = $this->processRatingDistribution($value['ReviewStatistics']['RatingDistribution'], $value['ReviewStatistics']['TotalReviewCount']);
          // To get the featured reviews.
          $bv_featured_reviews = [];
          if (!empty($result['Includes']) && isset($result['Includes']['Reviews'])) {
            foreach ($result['Includes']['Reviews'] as $review) {
              if ($review['ProductId'] === $value['Id']) {
                if (str_contains($review['ContentLocale'], 'en')
                  && empty($bv_featured_reviews['en'])) {
                  $bv_featured_reviews['en'] = $review['Title'];
                }
                if (str_contains($review['ContentLocale'], 'ar')
                  && empty($bv_featured_reviews['ar'])) {
                  $bv_featured_reviews['ar'] = $review['Title'];
                }
              }
            }
          }

          $response['ReviewStatistics'][$value['Id']] = [
            'OverallRatingPercentage' => round(($value['ReviewStatistics']['AverageOverallRating'] / 5) * 100),
            'AverageOverallRating' => round($value['ReviewStatistics']['AverageOverallRating'], 1),
            'TotalReviewCount' => $value['ReviewStatistics']['TotalReviewCount'],
            'RatingDistribution' => $rating_distribution['rating_distribution'],
            'RatingDistributionAverage' => $rating_distribution['rating_distribution_average'],
            'RatingStars' => ['rating_' . round($value['ReviewStatistics']['AverageOverallRating'])],
            'ProductRecommendedAverage' => round(($value['ReviewStatistics']['RecommendedCount'] / $value['ReviewStatistics']['TotalReviewCount']) * 100),
            'FeaturedReviews' => $bv_featured_reviews,
            'locations' => $locations['location_filter'],
          ];
        }
      }

      return $response;
    }

    return NULL;
  }

  /**
   * Truncate a float number.
   *
   * @param float $val
   *   Float number to be truncate.
   * @param int $f
   *   Number of precision.
   *
   * @return float
   *   Return a float value.
   */
  public function truncate($val, $f = 0) {
    if (($p = strpos($val, '.')) !== FALSE) {
      $val = floatval(substr($val, 0, $p + 1 + $f));
    }
    return $val;
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
   * @param int $review_total
   *   Total review count.
   *
   * @return array
   *   A rating range processed for algolia rating facet.
   */
  public function processRatingDistribution(array $rating, int $review_total) {
    if (empty($rating)) {
      return NULL;
    }

    usort($rating, fn($rating_value1, $rating_value2) => $rating_value2['RatingValue'] <=> $rating_value1['RatingValue']);

    $rating_range = [];
    // Rating stars and histogram data.
    foreach ($rating as $value) {
      $rating_range['rating_distribution'][] = 'rating_' . $value['RatingValue'] . '_' . $value['Count'];
      $average = round(($value['Count'] / $review_total) * 100);
      $rating_range['rating_distribution_average'][] = 'rating_' . $value['RatingValue'] . '_' . $average;
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
    $request_options = [];
    $extra_params = [
      'ProductId' => $product_id,
      'action' => '',
    ];
    $request = $this->alshayaBazaarVoiceApiHelper->getBvUrl('data/submitreview.json', $extra_params);
    $url = $request['url'];
    $request_options['query'] = $request['query'];

    $result = $this->alshayaBazaarVoiceApiHelper->doRequest('GET', $url, $request_options);

    if (!empty($result) && !$result['HasErrors'] && isset($result['Data']['Fields'])) {
      return $this->updateAlshayaBvWriteReviewWebForm($result['Data']['Fields']);
    }

    return NULL;
  }

  /**
   * Get list of photos stored for temporarily purpose.
   *
   * @return array
   *   list of photos.
   */
  public function getUploadedPhotos() {
    $directory_path = $this->fileSystem->realpath('public://review_photo_temp_upload');
    $photos = glob($directory_path . '/*');

    return $photos;
  }

  /**
   * Delete photos stored in folder temporarily.
   */
  public function deletePhotos($photos) {
    foreach ($photos as $photo) {
      try {
        if (is_file($photo)) {
          $this->fileSystem->unlink($photo);
        }
      }
      catch (FileException $e) {
        $this->logger->error($e->getMessage());
      }
    }
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
        if (str_contains($key, 'photo')) {
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
      // Get Configs for Google translation API.
      $google_translations_api = Settings::get('google_translations_api');
      $basic_configs['google_api_endpoint'] = $google_translations_api['endpoint'] ?? '';
      $basic_configs['google_api_key'] = $google_translations_api['api_key'] ?? '';
    }

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
      $term_parents = $this->entityTypeManager->getStorage('taxonomy_term')->loadAllParents($term['target_id']);
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
    $userId = alshaya_acm_customer_is_customer($this->currentUser, TRUE);
    $mail = $this->currentUser->getEmail();
    $productId = $this->currentRequest->get('product');

    // URL-encoded query string.
    $uasStr = "date=" . urlencode(date('Ymd')) . "&userid=" . urlencode($userId) . "&EmailAddress=" . urlencode($mail) . "&maxage=" . urlencode($maxAge);
    $userStr = ($productId !== NULL) ? $uasStr . '&verifiedpurchaser=True&subjectids=' . $productId : $uasStr;
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
    $this->moduleHandler->loadInclude('alshaya_bazaar_voice', 'inc', 'alshaya_bazaar_voice.static_strings');
    $strings = [
      '#theme' => 'alshaya_strings',
      '#bazaarvoice_strings' => _alshaya_bazaar_voice_static_strings(),
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
   * @param array $item
   *   Order item array of sku.
   *
   * @return array
   *   Details for all the products.
   */
  public function getMyAccountProductSettings($sku_id, array $basic_configs, array $item) {
    $productObj = new \stdClass();
    $productObj->alshaya_bazaar_voice = $this->getProductBazaarVoiceDetails($sku_id, $basic_configs, $item);
    // Add current user details.
    $productObj->productReview = $this->getProductReviewForCurrentUser($sku_id);

    return $productObj;
  }

  /**
   * Fetch drupal settings for individual product.
   *
   * @param mixed $sku
   *   SKU text or full entity object.
   * @param array $basic_configs
   *   Basic configurations of bazaarvoice.
   * @param array $item
   *   Item details from order.
   *
   * @return array|null
   *   Drupal settings with product details.
   */
  public function getProductBazaarVoiceDetails($sku, array $basic_configs, array $item = []) {
    $settings = [];
    $product_url = '';
    $product_label = '';
    $image_url = '';

    $sku_entity = $sku instanceof SKUInterface ? $sku : SKU::loadFromSku($sku);
    if (($sku_entity === NULL || $this->skuManager->isSkuFreeGift($sku_entity)) && !empty($item)) {
      if (empty($item['image'])) {
        $item['image'] = alshaya_acm_get_product_display_image(
          $item['sku'],
          SkuImagesHelper::STYLE_PRODUCT_TEASER,
          'cart'
        );
      }
      $image_url = $item['image'] ? file_create_url($item['image']['#uri']) : '';
      $product_label = $item['name'];
    }
    else {
      $productNode = $this->skuManager->getDisplayNode($sku_entity);
      if ($productNode instanceof NodeInterface) {
        // Disable BazaarVoice Rating and Review in PDP
        // if checkbox is checked for any categories or its Parent Categories.
        $category_based_config = $this->getCategoryBasedConfig($productNode);
        if (empty($category_based_config) || !$category_based_config['show_rating_reviews']) {
          return;
        }
        $media = $this->skuImagesManager->getFirstImage($sku_entity, 'pdp');
        if (!empty($media)) {
          $image_url = file_create_url($media['drupal_uri']);
        }
        $product_url = $productNode->toUrl()->toString();
        $product_label = $productNode->label();
      }
    }

    // Get country code.
    $country_code = _alshaya_custom_get_site_level_country_code();

    $settings = [
      'product' => [
        'url' => $product_url,
        'title' => $product_label,
        'image_url' => $image_url,
      ],
      'bazaar_voice' => [
        'stats' => 'Reviews',
        'country_code' => $country_code,
      ],
      'base_url' => $this->currentRequest->getSchemeAndHttpHost(),
      'bv_auth_token' => $this->currentRequest->get('bv_authtoken'),
      'customer_id' => alshaya_acm_customer_is_customer($this->currentUser, TRUE),
      'hide_fields_write_review' => $category_based_config['hide_fields_write_review'] ?? [],
    ];
    $settings['bazaar_voice'] = array_merge($settings['bazaar_voice'], $basic_configs);

    return $settings;
  }

  /**
   * Check if parent sku exists.
   *
   * @param string $sku_id
   *   Sku id.
   *
   * @return string|null
   *   return parent sku id|NULL.
   */
  public function checkParentSku($sku_id) {
    // Return parent sku if current sku is child sku.
    $parent_sku = $this->skuManager->getParentSkuBySku($sku_id);
    if ($parent_sku !== NULL && $parent_sku instanceof SKUInterface) {
      return $parent_sku->getSku();
    }
    // Do not allow write a review for child sku.
    $sku_entity = $sku_id instanceof SKU ? $sku_id : SKU::loadFromSku($sku_id);
    if (!empty($sku_entity)) {
      $color = $sku_entity->get('attr_color')->getString();
      $size = $sku_entity->get('attr_size')->getString();
      if (!empty($color) && !empty($size)) {
        return NULL;
      }
    }

    return $sku_id;
  }

  /**
   * Get product info reviewed by current user.
   *
   * @param string $sku_id
   *   Sku Id.
   *
   * @return array|null
   *   returns product review status and rating.
   */
  public function getProductReviewForCurrentUser($sku_id) {
    $request_options = [];
    // Get sanitized sku.
    $sanitized_sku = $this->skuManager->getSanitizedSku($sku_id);
    $config = $this->configFactory->get('bazaar_voice.settings');
    $myaccount_reviews_limit = $config->get('myaccount_reviews_limit');
    $customer_id = alshaya_acm_customer_is_customer($this->currentUser, TRUE);
    $extra_params = [
      'filter' => 'authorid:' . $customer_id,
      'Include' => 'Authors,Products',
      'stats' => 'Reviews',
      'Limit' => $myaccount_reviews_limit,
    ];
    $request = $this->alshayaBazaarVoiceApiHelper->getBvUrl('data/reviews.json', $extra_params);
    if (isset($request['url']) && isset($request['query'])) {
      $url = $request['url'];
      $request_options['query'] = $request['query'];
      $result = $this->alshayaBazaarVoiceApiHelper->doRequest('GET', $url, $request_options);
      if (!empty($result) && !$result['HasErrors'] && !empty($result['Includes'])) {
        if (!empty($result['Results'])) {
          foreach ($result['Results'] as $review) {
            if ($review['ProductId'] === $sanitized_sku) {
              $productReviewData = [
                'review_data' => $review,
                'user_rating' => $review['Rating'],
              ];
              return $productReviewData;
            }
          }
        }
      }
    }
    return NULL;
  }

  /**
   * Get review statistics of a product.
   *
   * @param string $product_id
   *   Product id or sanitized sku id.
   *
   * @return array|null
   *   returns product review statistics and rating.
   */
  public function getProductReviewStatistics(string $product_id) {
    $static = [];
    $request_options = [];
    static $response = [];
    $config = $this->configFactory->get('bazaar_voice.settings');
    $pdp_reviews_seo_limit = $config->get('pdp_reviews_seo_limit');
    if (isset($response[$product_id]) && !empty($response[$product_id])) {
      return $static[$product_id];
    }
    $extra_params = [
      'filter' => 'id:' . $product_id,
      'stats' => 'reviews',
      'include' => 'Reviews',
      'sort_reviews' => 'submissiontime:desc',
      'Limit_Reviews' => $pdp_reviews_seo_limit,
    ];
    $request = $this->alshayaBazaarVoiceApiHelper->getBvUrl('data/products.json', $extra_params);

    if (empty($request)) {
      return;
    }

    $url = $request['url'];
    $request_options['query'] = $request['query'];

    $result = $this->alshayaBazaarVoiceApiHelper->doRequest('GET', $url, $request_options);
    if (!empty($result) && !$result['HasErrors'] && !empty($result['Results'])) {
      $response[$product_id]['productData'] = $result['Results'][0];
      if (isset($result['Includes']['Reviews'])) {
        foreach ($result['Includes']['Reviews'] as $review) {
          $response[$product_id]['reviews'][] = $review;
        }
      }
      return $response;
    }

    return NULL;
  }

}
