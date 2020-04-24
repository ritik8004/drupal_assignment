<?php

namespace Drupal\alshaya_hm_images\EventSubscriber;

use Drupal\alshaya_acm_product\Event\ProductUpdatedEvent;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\file\FileInterface;
use Drupal\file\FileUsage\FileUsageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\alshaya_hm_images\SkuAssetManager;

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
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  private $logger;

  /**
   * File usage.
   *
   * @var \Drupal\file\FileUsage\FileUsageInterface
   */
  private $fileUsage;

  /**
   * SKU Assets Manager.
   *
   * @var \Drupal\alshaya_hm_images\SkuAssetManager
   */
  private $skuAssetsManager;

  /**
   * ProductUpdatedEventSubscriber constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Factory.
   * @param \Drupal\file\FileUsage\FileUsageInterface $file_usage
   *   File usage.
   * @param \Drupal\alshaya_hm_images\SkuAssetManager $sku_assets_manager
   *   SKU Assets Manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              LoggerChannelFactoryInterface $logger_factory,
                              FileUsageInterface $file_usage,
                              SkuAssetManager $sku_assets_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger_factory->get('alshaya_hm_images');
    $this->fileUsage = $file_usage;
    $this->skuAssetsManager = $sku_assets_manager;
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
          // Remove usage of file.
          $this->fileUsage->delete($file, $entity->getEntityTypeId(), $entity->getEntityTypeId(), $entity->id());
          // Delete file if there is no usage and it is not a video.
          // Video files are used across markets of the brand
          // so even if the usage on this site is empty,
          // it might be used by another market.
          if (empty($this->fileUsage->listUsage($file))
            && ($this->skuAssetsManager->getAssetType($asset) !== 'video')) {
            $this->logger->notice('Deleting file @fid for sku @sku as it is getting deleted', [
              '@fid' => $file->id(),
              '@sku' => $entity->getSku(),
            ]);

            $file->delete();
          }
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
