<?php

namespace Drupal\alshaya_acm_product\EventSubscriber;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Event\AcqSkuValidateEvent;
use Drupal\alshaya_acm_product\Service\ProductProcessedManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AlshayaAcmProductAcqSkuValidateEventSubscriber.
 *
 * @package Drupal\acq_sku\EventSubscriber
 */
class AlshayaAcmProductAcqSkuValidateEventSubscriber implements EventSubscriberInterface {

  /**
   * Product Processed Manager.
   *
   * @var \Drupal\alshaya_acm_product\Service\ProductProcessedManager
   */
  protected $productProcessedManager;

  /**
   * The Logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * Constructs event subscriber.
   *
   * @param \Drupal\alshaya_acm_product\Service\ProductProcessedManager $product_processed_manager
   *   Product Processed Manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The Logger factory object.
   */
  public function __construct(ProductProcessedManager $product_processed_manager,
                              LoggerChannelFactoryInterface $logger_factory) {
    $this->productProcessedManager = $product_processed_manager;
    $this->logger = $logger_factory->get('AlshayaAcmProductAcqSkuValidateEventSubscriber');
  }

  /**
   * Sku validate event handler.
   *
   * @param \Drupal\acq_sku\Event\AcqSkuValidateEvent $event
   *   Acq sku validate event.
   */
  public function onValidate(AcqSkuValidateEvent $event) {
    $product = $event->getProduct();

    if (!$this->validateConfigurableProduct($product)) {
      // Validation failed for new data received, we set the status to 0.
      $product['status'] = 0;

      // No further checks required as we are going to delete this product.
      $event->stopPropagation();
    }

    /*
     * Status: 1 means enabled
     * Status: 2 means disabled
     * Status: 0 - used internally to mark as disabled
     * Visibility: 1 means visible on frontend
     * Visibility: 2 means not visible on frontend
     */
    if ($product['status'] == 2 && $product['visibility'] == 1) {
      $sku = SKU::loadFromSku($product['sku'], '', FALSE, FALSE);
      if ($sku instanceof SKUInterface) {
        // For now just remove node by removing product visibility.
        $product['status'] = 1;
        $product['visibility'] = 2;

        // Remove the mapping so it is not used on web if variant.
        $this->productProcessedManager->removeProduct($product['sku']);

        $this->logger->notice('Keeping product as not visible on frontend to delete later: @sku', [
          '@sku' => $product['sku'],
        ]);
      }
    }

    $event->setProduct($product);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      AcqSkuValidateEvent::ACQ_SKU_VALIDATE => ['onValidate', 100],
    ];
  }

  /**
   * Additional validations for configurable product.
   *
   * @param array $product
   *   Product data.
   *
   * @return bool
   *   FALSE if validation failed.
   */
  private function validateConfigurableProduct(array $product) {
    // We only deal with the configurable skus.
    if ($product['type'] != 'configurable') {
      return TRUE;
    }

    foreach ($product['extension']['configurable_product_options'] as $options) {
      if (count($options['values']) == 0) {
        $this->logger->warning('Data received for configurable sku @sku has corrupt data in configurable_product_options.', [
          '@sku' => $product['sku'],
        ]);

        return FALSE;
      }
    }

    return TRUE;
  }

}
