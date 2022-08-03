<?php

namespace Drupal\alshaya_acm_promotion\Plugin\rest\resource;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm\CartData;
use Drupal\alshaya_acm_product\AlshayaRequestContextManager;
use Drupal\alshaya_acm_promotion\AlshayaPromoLabelManager;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\rest\Plugin\ResourceBase;
use http\Exception\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a resource to get dynamic promo labels.
 *
 * @RestResource(
 *   id = "promotions_dynamic_label",
 *   label = @Translation("Promotions dynamic labels"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/promotions/dynamic-label-product/{sku}"
 *   }
 * )
 */
class PromotionsDynamicLabelsResource extends ResourceBase {

  /**
   * Alshaya Promotions Label Manager.
   *
   * @var \Drupal\alshaya_acm_promotion\AlshayaPromoLabelManager
   */
  protected $promoLabelManager;

  /**
   * PromotionsResource constructor.
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
   * @param \Drupal\alshaya_acm_promotion\AlshayaPromoLabelManager $alshayaPromoLabelManager
   *   Alshaya Promo Label Manager.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              array $serializer_formats,
                              LoggerInterface $logger,
                              AlshayaPromoLabelManager $alshayaPromoLabelManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->promoLabelManager = $alshayaPromoLabelManager;
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
      $container->get('logger.factory')->get('alshaya_acm_promotion'),
      $container->get('alshaya_acm_promotion.label_manager')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @param string $sku
   *   Product Sku.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request stack.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *   Response containing promotion dynamic labels.
   */
  public function get(string $sku, Request $request) {
    $sku = base64_decode($sku);
    $sku = SKU::loadFromSku($sku);

    // Add cache metadata.
    $cache_array = [
      'tags' => ['node_type:acq_promotion'],
      'contexts' => [
        'session',
        'languages',
        'url.query_args:context',
      ],
    ];

    try {
      if (!($sku instanceof SKUInterface)) {
        throw new InvalidArgumentException();
      }

      $get = $request->query->all();
      $cart = CartData::createFromArray($get);
    }
    catch (\InvalidArgumentException) {
      $response = new CacheableJsonResponse([]);
      $response->addCacheableDependency(CacheableMetadata::createFromRenderArray(['#cache' => $cache_array]));
      return $response;
    }

    Cache::mergeTags($cache_array['tags'], $sku->getCacheTags());
    Cache::mergeTags($cache_array['tags'], $cart->getCacheTags());

    // We use app as default here as we have updated web code and APP
    // code will be updated later to pass the value all the time.
    // So if someone invokes this without the context, we use app as default.
    AlshayaRequestContextManager::updateDefaultContext('app');
    $label = $this->promoLabelManager->getSkuPromoDynamicLabel($sku);
    $response = new CacheableJsonResponse(['label' => $label]);
    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray(['#cache' => $cache_array]));
    return $response;
  }

}
