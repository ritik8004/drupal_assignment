<?php

namespace Drupal\alshaya_hm_temp_skus_filter\EventSubscriber;

use Drupal\acq_sku\Event\AcqSkuValidateEvent;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Alshaya HnM event subscriber.
 */
class AlshayaHnmSkuValidator implements EventSubscriberInterface {

  /**
   * The Logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * Constructs event subscriber.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The Logger factory object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory,
                              ConfigFactoryInterface $config_factory) {
    $this->logger = $logger_factory->get('acq_sku');
    $this->configFactory = $config_factory;
  }

  /**
   * Sku validate event handler.
   *
   * @param \Drupal\acq_sku\Event\AcqSkuValidateEvent $event
   *   Acq sku validate event.
   */
  public function onValidate(AcqSkuValidateEvent $event) {
    $product = $event->getProduct();
    $skip_skus_without_multipack = $this->configFactory->get('alshaya_hm_temp_skus_filter.config')->get('skip_skus_without_multipack');

    if ($product['type'] == 'configurable') {
      return;
    }

    // Skip the product by default. Also, for case where we have no assets with
    // the SKU.
    $product['skip'] = TRUE;

    // Check for assets to contain valid image types for season 6+ products.
    if (isset($product['extension'], $product['extension']['assets'])) {
      $assets = $product['extension']['assets'];

      foreach ($assets as $asset) {
        // Ignore season 5 assets & assets missing a type.
        if (empty($asset['Data']['AssetType']) || !empty($asset['is_old_format'])) {
          continue;
        }

        // Avoid skipping product import only if we find an asset with
        // DescriptiveStillLife image & multipack attribute set to TRUE.
        if ($asset['Data']['AssetType'] === 'StillMediaComponents/Product/DescriptiveStillLife') {
          if (($skip_skus_without_multipack) && ($asset['Data']['isMultiPack'] == 'true')) {
            $product['skip'] = FALSE;
            break;
          }
          elseif (!$skip_skus_without_multipack) {
            $product['skip'] = FALSE;
            break;
          }
        }
      }
    }

    $event->setProduct($product);

    if ($product['skip']) {
      $this->logger->info('SKU @sku missing asset with DescriptiveStillLife @multipack. Skipping import.', ['@sku' => $product['sku'], '@multipack' => $skip_skus_without_multipack ? ' & multipack set to "true"' : '']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      AcqSkuValidateEvent::ACQ_SKU_VALIDATE => ['onValidate'],
    ];
  }

}
