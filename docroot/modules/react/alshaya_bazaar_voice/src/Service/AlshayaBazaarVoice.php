<?php

namespace Drupal\alshaya_bazaar_voice\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Database\Driver\mysql\Connection;

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
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              EntityTypeManagerInterface $entity_type_manager,
                              Connection $connection,
                              AlshayaBazaarVoiceApiHelper $alshaya_bazaar_voice_api_helper) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->connection = $connection;
    $this->alshayaBazaarVoiceApiHelper = $alshaya_bazaar_voice_api_helper;
  }

  /**
   * Get reviews data from BV api.
   *
   * @param array $skus
   *   batch of Skus.
   *
   * @return array
   *   BV attributes data to be indexed in algolia.
   */
  public function getDataFromBvReviewFeeds(array $skus) {
    $skus = implode(",", $skus);
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
        $response['ReviewStatistics'][$value['Id']] = [
          'AverageOverallRating' => $value['ReviewStatistics']['AverageOverallRating'],
          'TotalReviewCount' => $value['ReviewStatistics']['TotalReviewCount'],
          'RatingDistribution' => $this->processRatingDistribution($value['ReviewStatistics']['RatingDistribution']),
        ];
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
    foreach ($rating as $value) {
      $rating_range[] = 'rating_' . $value['RatingValue'] . '_' . $value['Count'];
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
    if ($fields) {
      $bv_form_fields = [];
      foreach ($fields as $key => $value) {
        $key = preg_replace("/[^A-Za-z0-9-]/", '_', $key);
        $key = strtolower($key);
        $id = preg_replace("/[^A-Za-z0-9-]/", '_', $value['Id']);
        switch ($value['Type']) {
          case 'TextInput':
          case 'IntegerInput':
            $bv_form_fields[$key] = [
              '#type' => 'textfield',
              '#required' => $value['Required'],
              '#title' => $value['Label'],
              '#value' => $value['Value'],
              '#minlength' => $value['MinLength'],
              '#id' => $id,
              '#maxlength' => $value['MaxLength'],
              '#default_value' => $value['Default'],
            ];
            break;

          case 'BooleanInput':
            $bv_form_fields[$key] = [
              '#type' => 'checkbox',
              '#required' => $value['Required'],
              '#title' => $value['Label'],
              '#value' => $value['Value'],
              '#id' => $id,
              '#default_value' => $value['Default'],
            ];
            break;

          case 'TextAreaInput':
            $bv_form_fields[$key] = [
              '#type' => 'textarea',
              '#required' => $value['Required'],
              '#title' => $value['Label'],
              '#value' => $value['Value'],
              '#minlength' => $value['MinLength'],
              '#id' => $id,
              '#maxlength' => $value['MaxLength'],
              '#default_value' => $value['Default'],
            ];
            break;

          case 'SelectInput':
            $options = $this->processOptionsField($value['Options']);
            $bv_form_fields[$key] = [
              '#type' => 'select',
              '#title' => $value['Label'],
              '#required' => $value['Required'],
              '#default_value' => $options['default'],
              '#options' => $options['options'],
              '#id' => $id,
            ];
            break;

          default:
            break;
        }
      }

      // Update/Add BazaarVoice webform fields.
      $webforms = $this->entityTypeManager->getStorage('webform')
        ->loadByProperties(['id' => self::ALSHAYA_BAZAARVOICE_FORM_ID]);

      $webform = reset($webforms);
      $new_element = array_merge($webform->getElementsDecoded(), $bv_form_fields);

      return $webform->setElements($new_element)->save();
    }
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
   * BazaarVoice web form fields configs.
   *
   * @return array
   *   BazaarVoice web form fields configs.
   */
  public function getBazaarVoiceFormConfig() {
    $webforms = $this->entityTypeManager->getStorage('webform')
      ->loadByProperties(['id' => self::ALSHAYA_BAZAARVOICE_FORM_ID]);
    $webform = reset($webforms);

    return $webform->getElementsDecoded();
  }

}
