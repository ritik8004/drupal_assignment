<?php

namespace Drupal\alshaya_acm_product\Plugin\rest\resource;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\AcqSkuLinkedSku;
use Drupal\acq_sku\Entity\SKU;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\alshaya_acm_product\Service\SkuInfoHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get product details with linked skus.
 *
 * @RestResource(
 *   id = "product_linked_skus",
 *   label = @Translation("Product Linked Skus"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/product/{sku}/linked"
 *   }
 * )
 */
class ProductLinkedSkusResource extends ResourceBase {

  /**
   * Sku info helper.
   *
   * @var \Drupal\alshaya_acm_product\Service\SkuInfoHelper
   */
  private $skuInfoHelper;

  /**
   * ProductResource constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param array $serializer_formats
   *   Serializer formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger channel.
   * @param \Drupal\alshaya_acm_product\Service\SkuInfoHelper $sku_info_helper
   *   TSku info helper.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    SkuInfoHelper $sku_info_helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->skuInfoHelper = $sku_info_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('alshaya_acm_product'),
      $container->get('alshaya_acm_product.sku_info')
    );
  }

  /**
   * Responds to GET requests.
   *
   * Returns linked skus of the given sku.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing linked skus of the given sku.
   */
  public function get(string $sku) {
    $skuEntity = SKU::loadFromSku($sku);

    if (!$skuEntity instanceof SKUInterface) {
      throw new NotFoundHttpException($this->t("page not found"));
    }

    $data = [];
    foreach (AcqSkuLinkedSku::LINKED_SKU_TYPES as $linked_type) {
      $data['linked'][] = [
        'link_type' => $linked_type,
        'skus' => $this->getLinkedSkus($skuEntity, $linked_type),
      ];
    }

    $response = new ResourceResponse($data);

    $cacheableMetadata = $response->getCacheableMetadata();
    $cacheableMetadata->addCacheTags($skuEntity->getCacheTags());
    $cacheableMetadata->addCacheContexts($skuEntity->getCacheContexts());
    $response->addCacheableDependency($cacheableMetadata);

    return $response;
  }

  /**
   * Get fully loaded linked skus.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string $linked_type
   *   Linked type.
   *
   * @return array
   *   Linked SKUs.
   */
  protected function getLinkedSkus(SKUInterface $sku, string $linked_type) {
    $return = [];
    $linkedSkus = $this->skuInfoHelper->getLinkedSkus($sku, $linked_type);
    foreach (array_keys($linkedSkus) as $linkedSku) {
      $linkedSkuEntity = SKU::loadFromSku($linkedSku);
      if ($lightProduct = $this->skuInfoHelper->getLightProduct($linkedSkuEntity)) {
        $return[] = $lightProduct;
      }
    }
    return $return;
  }

}
