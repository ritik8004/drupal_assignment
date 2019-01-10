<?php

namespace Drupal\acq_sku\EventSubscriber;

use Drupal\acq_sku\Event\AcqSkuValidateEvent;
use Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType\Configurable;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AlshayaAcmProductAcqSkuValidateEventSubscriber.
 *
 * @package Drupal\acq_sku\EventSubscriber
 */
class AlshayaAcmProductAcqSkuValidateEventSubscriber implements EventSubscriberInterface {

  /**
   * The Logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $logger;

  /**
   * Constructs event subscriber.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The Logger factory object.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory) {
    $this->logger = $logger_factory->get(__CLASS__);
  }

  /**
   * Sku validate event handler.
   *
   * @param \Drupal\acq_sku\Event\AcqSkuValidateEvent $event
   *   Acq sku validate event.
   */
  public function onValidate(AcqSkuValidateEvent $event) {
    $product = $event->getProduct();

    if ($this->validateConfigurableProduct($product)) {
      return;
    }

    // Validation failed for new data received, we set the status to 0.
    $product['status'] = 0;
    $event->setProduct($product);

    // No further checks required as we are going to delete this product.
    $event->stopPropagation();
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
    // Simply return true for non-configurable products.
    if ($product['type'] != Configurable::class) {
      return TRUE;
    }

    $options_count = count(reset($product['extension']['configurable_product_options'])['values']);
    foreach ($product['extension']['configurable_product_options'] as $options) {
      if (count($options['values']) != $options_count) {
        $this->logger->warning('Data received for configurable sku @sku has corrupt data in configurable_product_options.', [
          '@sku' => $product['sku'],
        ]);

        return FALSE;
      }
    }

    return TRUE;
  }

}
