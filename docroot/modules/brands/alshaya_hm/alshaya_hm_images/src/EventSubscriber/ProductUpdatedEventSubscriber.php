<?php

namespace Drupal\alshaya_hm_images\EventSubscriber;

use Drupal\alshaya_acm_product\Event\ProductUpdatedEvent;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\FileInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductUpdatedEventSubscriber.
 *
 * @package Drupal\alshaya_hm_images\EventSubscriber
 */
class ProductUpdatedEventSubscriber implements EventSubscriberInterface {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * ProductUpdatedEventSubscriber constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[ProductUpdatedEvent::EVENT_NAME][] = ['onProductDeleted', 999];
    return $events;
  }

  /**
   * Subscriber Callback for the event.
   *
   * @param \Drupal\alshaya_acm_product\Event\ProductUpdatedEvent $event
   *   Event object.
   */
  public function onProductDeleted(ProductUpdatedEvent $event) {
    if ($event->getOperation() != ProductUpdatedEvent::EVENT_DELETE) {
      return;
    }

    $entity = $event->getSku();
    $assets = unserialize($entity->get('attr_assets')->getString()) ?? [];

    foreach ($assets as $asset) {
      if (isset($asset['fid'])) {
        $file = $this->getFileStorage()->load($asset['fid']);
        if ($file instanceof FileInterface) {
          $file->delete();
        }
      }
    }
  }

  /**
   * Wrapper function to get File Storage.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   File Storage.
   */
  private function getFileStorage() {
    static $storage;

    if (empty($storage)) {
      $storage = $this->entityTypeManager->getStorage('file');
    }

    return $storage;
  }

}
