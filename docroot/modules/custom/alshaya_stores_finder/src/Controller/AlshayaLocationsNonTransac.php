<?php

namespace Drupal\alshaya_stores_finder\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class Alshaya Locations Controller Non Transac.
 */
class AlshayaLocationsNonTransac extends ControllerBase {

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
   * AlshayaLocationsNonTransac constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   EntityTypeManagerInterface service object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   LanguageManagerInterface service object.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LanguageManagerInterface $languageManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('language_manager'),
    );
  }

  /**
   * Stores list for the brand non-transac site.
   *
   * @return object
   *   Stores list fetched from the database(store content type).
   */
  public function stores() {
    $languageId = $this->languageManager->getCurrentLanguage()->getId();
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
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
