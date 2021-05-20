<?php

namespace Drupal\alshaya_bazaar_voice\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\Session\AccountProxy;
use Symfony\Component\Yaml\Yaml;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\alshaya_acm_product\SkuManager;

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
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              EntityTypeManagerInterface $entity_type_manager,
                              Connection $connection,
                              AlshayaBazaarVoiceApiHelper $alshaya_bazaar_voice_api_helper,
                              AccountProxy $current_user,
                              EntityRepositoryInterface $entityRepository,
                              SkuManager $sku_manager) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->connection = $connection;
    $this->alshayaBazaarVoiceApiHelper = $alshaya_bazaar_voice_api_helper;
    $this->currentUser = $current_user;
    $this->entityRepository = $entityRepository;
    $this->skuManager = $sku_manager;
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
   * Get product info reviewed by current user.
   *
   * @param string $productId
   *   product Id.
   *
   * @return array
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
