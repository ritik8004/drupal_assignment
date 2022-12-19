<?php

namespace Drupal\alshaya_bazaar_voice\Plugin\rest\resource;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Drupal\Core\Cache\Cache;
use Drupal\node\NodeInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get configurations for write a review form.
 *
 * @RestResource(
 *   id = "write_review_config",
 *   label = @Translation("Write a review config"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/bv/config/write-review/{main_sku}"
 *   }
 * )
 */
class WriteReviewConfigResource extends ResourceBase {

  /**
   * Alshaya BazaarVoice Service.
   *
   * @var Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice
   */
  protected $alshayaBazaarVoice;

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * The mobile app utility service.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   */
  protected $mobileAppUtility;

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
   * @param Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice $alshaya_bazaar_voice
   *   Alshaya BazaarVoice service.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   The mobile app utility service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AlshayaBazaarVoice $alshaya_bazaar_voice,
    SkuManager $sku_manager,
    MobileAppUtility $mobile_app_utility
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->alshayaBazaarVoice = $alshaya_bazaar_voice;
    $this->skuManager = $sku_manager;
    $this->mobileAppUtility = $mobile_app_utility;
    $this->cache = [
      'tags' => [],
      'contexts' => [],
    ];
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
      $container->get('logger.factory')->get('alshaya_mobile_app'),
      $container->get('alshaya_bazaar_voice.service'),
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('alshaya_mobile_app.utility')
    );
  }

  /**
   * Responds to GET requests.
   *
   * Returns response data for BazaarVoice write review configurations.
   *
   * @param string $main_sku
   *   Product main sku.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing BazaarVoice write review configurations.
   */
  public function get($main_sku = NULL) {
    $data = [];
    if (empty($main_sku)) {
      $this->mobileAppUtility->throwException();
    }

    $skuEntity = SKU::loadFromSku($main_sku);

    if (!($skuEntity instanceof SKU)) {
      $this->mobileAppUtility->throwException();
    }

    // Load the node object.
    $productNode = $this->skuManager->getDisplayNode($skuEntity);
    if ($productNode instanceof NodeInterface) {
      // Get category based configs for write review.
      $category_based_config = $this->alshayaBazaarVoice->getCategoryBasedConfig($productNode);

      if (empty($category_based_config) || !$category_based_config['show_rating_reviews']) {
        $this->mobileAppUtility->throwException();
      }
      $this->cache['tags'] = Cache::mergeTags($this->cache['tags'], $productNode->getCacheTags());
      $this->cache['contexts'] = Cache::mergeTags($this->cache['contexts'], $productNode->getCacheContexts());
    }
    elseif (!$this->skuManager->isSkuFreeGift($skuEntity)) {
      $this->mobileAppUtility->throwException();
    }

    $this->cache['tags'] = Cache::mergeTags($this->cache['tags'], $skuEntity->getCacheTags());
    $this->cache['contexts'] = Cache::mergeTags($this->cache['contexts'], $skuEntity->getCacheContexts());

    $data['write_review_form'] = $this->alshayaBazaarVoice->getWriteReviewFieldsConfig();
    $data['hide_fields_write_review'] = $category_based_config['hide_fields_write_review'] ?? [];

    $response = new ResourceResponse($data);
    $cacheableMetadata = $response->getCacheableMetadata();
    $cacheableMetadata->addCacheTags($this->cache['tags']);
    $cacheableMetadata->addCacheContexts($this->cache['contexts']);
    $response->addCacheableDependency($cacheableMetadata);

    return $response;
  }

}
