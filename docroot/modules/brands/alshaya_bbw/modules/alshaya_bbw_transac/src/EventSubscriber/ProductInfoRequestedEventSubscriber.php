<?php

namespace Drupal\alshaya_bbw_transac\EventSubscriber;

use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\ProductInfoRequestedEvent;
use Drupal\alshaya_acm_product\EventSubscriber\ProductInfoRequestedBaseEventSubscriber;
use Drupal\alshaya_acm_product\ProductHelper;
use Drupal\alshaya_acm_product\SkuManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class ProductInfoRequestedEventSubscriber.
 *
 * @package Drupal\alshaya_bbw_transac\EventSubscriber
 */
class ProductInfoRequestedEventSubscriber extends ProductInfoRequestedBaseEventSubscriber implements EventSubscriberInterface {

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * Config Factory service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * Product helper service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $productHelper;

  /**
   * ProductInfoRequestedEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\alshaya_acm_product\ProductHelper $product_helper
   *   Product helper service object.
   */
  public function __construct(
    SkuManager $sku_manager,
    ConfigFactoryInterface $config_factory,
    ProductHelper $product_helper
  ) {
    $this->skuManager = $sku_manager;
    $this->configFactory = $config_factory;
    $this->productHelper = $product_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];

    $events[ProductInfoRequestedEvent::EVENT_NAME][] = [
      'onProductInfoRequested',
      800,
    ];

    return $events;
  }

  /**
   * Subscriber Callback for the event.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function onProductInfoRequested(ProductInfoRequestedEvent $event) {
    switch ($event->getFieldCode()) {
      case 'description':
        $this->processDescription($event);
        break;

      case 'short_description':
        $this->processShortDescription($event);
        break;
    }
  }

  /**
   * Process description for SKU.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function processDescription(ProductInfoRequestedEvent $event) {
    $sku_entity = $event->getSku();
    $description = $this->getDescription($sku_entity);
    $event->setValue($description['description']);
  }

  /**
   * Process short descriptions for SKU.
   *
   * @param \Drupal\acq_sku\ProductInfoRequestedEvent $event
   *   Event object.
   */
  public function processShortDescription(ProductInfoRequestedEvent $event) {
    $sku_entity = $event->getSku();
    $description = $this->getDescription($sku_entity);
    $event->setValue($description['short_desc']);
  }

  /**
   * Prepare description and short description array for given sku.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   The sku entity.
   *
   * @return array
   *   Return array of description and short description.
   */
  protected function prepareDescription(SKU $sku_entity) {
    $return = [];
    if ($fragrance_description = $sku_entity->get('attr_fragrance_description')->getString()) {
      $return['description'][] = [
        'label' => ['#markup' => $this->t('Fragrance Description')],
        'value' => ['#markup' => $fragrance_description],
      ];
    }

    if ($overview = $sku_entity->get('attr_description')->getValue()) {
      $return['description'][] = [
        'label' => ['#markup' => $this->t('Overview')],
        'value' => ['#markup' => $overview[0]['value']],
      ];
    }

    if ($usage = $sku_entity->get('attr_usage')->getString()) {
      $return['description'][] = [
        'label' => ['#markup' => $this->t('Usage')],
        'value' => ['#markup' => $usage],
      ];
    }

    if ($more_info = $sku_entity->get('attr_more_info')->getString()) {
      $return['description'][] = [
        'label' => ['#markup' => $this->t('More info')],
        'value' => ['#markup' => $more_info],
      ];
    }

    // $short_desc contains the description that should be
    // displayed before 'Read More'.
    $short_desc = $return['description'][0];
    // If short description not available, check other consecutive fields.
    if (empty($short_desc['value']['#markup'])) {
      foreach ($return['description'] as $short_description) {
        // If value is available in next field, then
        // use it and no need to process further.
        if (!empty($short_description['value']['#markup'])) {
          $short_desc = $short_description;
          break;
        }
      }
    }
    $return['short_desc'] = $short_desc;
    return $return;
  }

}
