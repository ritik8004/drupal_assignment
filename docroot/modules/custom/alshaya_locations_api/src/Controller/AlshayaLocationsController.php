<?php

namespace Drupal\alshaya_locations_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Entity\Query\QueryFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\alshaya_api\AlshayaApiWrapper;

/**
 * Class Alshaya Locations Controller.
 */
class AlshayaLocationsController extends ControllerBase {

  /**
   * QueryFactory service object.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The lconfigfactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Api wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $alshayaApi;

  /**
   * AlshayaLocationsController constructor.
   *
   * @param \Drupal\Core\Entity\Query\QueryFactory $entityQuery
   *   QueryFactory service object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   EntityTypeManagerInterface service object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   LanguageManagerInterface service object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config object.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $alshayaApi
   *   Config object.
   */
  public function __construct(QueryFactory $entityQuery, EntityTypeManagerInterface $entityTypeManager, LanguageManagerInterface $languageManager, ConfigFactoryInterface $configFactory, AlshayaApiWrapper $alshayaApi) {
    $this->entityQuery = $entityQuery;
    $this->entityTypeManager = $entityTypeManager;
    $this->languageManager = $languageManager;
    $this->configFactory = $configFactory;
    $this->alshayaApi = $alshayaApi;
  }

  /**
   * Inherits doc.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query'),
      $container->get('entity_type.manager'),
      $container->get('language_manager'),
      $container->get('config.factory'),
      $container->get('alshaya_api.api'),
    );
  }

  /**
   * Stores controller for site.
   *
   * @return object
   *   Click and collect for site.
   */
  public function stores($data = NULL) {
    // Mock file read for now.
    $url = ltrim($this->configFactory->get('alshaya_stores_finder.settings')->get('filter_path'), '/');
    $result = $this->alshayaApi->invokeApi($url, [], 'GET');

    return new JsonResponse(json_decode($result));
  }

  /**
   * Stores controller for site.
   *
   * @return object
   *   Click and collect for site.
   */
  public function local($data = NULL) {
    $languageId = $this->languageManager->getCurrentLanguage()->getId();
    $query = $this->entityQuery->get('node');
    $query->condition('status', 1);
    $query->condition('type', 'store');
    $query->condition('langcode', $languageId);
    $entity_ids = $query->execute();

    $node_storage = $this->entityTypeManager->getStorage('node');
    $nodes = $node_storage->loadMultiple($entity_ids);
    $data = [];
    $fields = [
      'store_name' => 'title',
      'store_code' => 'field_store_locator_id',
      'latitude' => 'field_latitude_longitude',
      'longitude' => 'field_latitude_longitude',
      'store_hours' => 'field_store_open_hours',
      'sts_delivery_time_label' => 'field_store_sts_label',
      'store_phone' => 'field_store_phone',
      'address' => 'field_store_address',
    ];
    foreach ($nodes as $node) {
      $node_data = [];
      if ($node->hasTranslation($languageId)) {
        $node = $node->getTranslation($languageId);
      }
      foreach ($fields as $field => $field_name) {
        switch ($field) {
          case 'latitude':
            $node_data[$field] = $node->get($field_name)->getValue()[0]['lat'];
            break;

          case 'longitude':
            $node_data[$field] = $node->get($field_name)->getValue()[0]['lng'];
            break;

          case 'address':
            $address = [];
            $address[] = [
              "code" => "street",
              "value" => $node->$field_name->value,
            ];
            $node_data[$field] = $address;
            break;

          case 'store_hours':
            $hours = [];
            foreach ($node->get($field_name)->getValue() as $hour) {
              $hour['label'] = $hour['key'];
              $hours[] = $hour;
            }
            $node_data[$field] = $hours;
            break;

          default:
            $node_data[$field] = $node->$field_name->value;
        }

      }
      $data['items'][] = $node_data;
    }

    return new JsonResponse(($data));
  }

}
