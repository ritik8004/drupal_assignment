<?php

namespace Drupal\alshaya_acm_product\Plugin\rest\resource;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_api\AlshayaI18nHelper;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Provides a resource for getting product urls and images list for SKU.
 *
 * @RestResource(
 *   id = "sku_images",
 *   label = @Translation("SKU Images and Product URLs"),
 *   uri_paths = {
 *     "canonical" = "/skus/list",
 *     "https://www.drupal.org/link-relations/create" = "/skus/list"
 *   }
 * )
 */
class SKUImagesResource extends ResourceBase {

  /**
   * SKU Images Manager service object.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  private $skuImagesManager;

  /**
   * Alshaya I18n Helper service object.
   *
   * @var \Drupal\alshaya_api\AlshayaI18nHelper
   */
  private $i18nHelper;

  /**
   * SKUImagesResource constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU Images Manager service object.
   * @param \Drupal\alshaya_api\AlshayaI18nHelper $i18n_helper
   *   Alshaya I18n Helper service object.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              array $serializer_formats,
                              LoggerInterface $logger,
                              SkuImagesManager $sku_images_manager,
                              AlshayaI18nHelper $i18n_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->skuImagesManager = $sku_images_manager;
    $this->i18nHelper = $i18n_helper;
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
      $container->get('logger.factory')->get('alshaya_api'),
      $container->get('alshaya_acm_product.sku_images_manager'),
      $container->get('alshaya_api.i18n_helper')
    );
  }

  /**
   * Responds to POST requests.
   *
   * Returns a url and images for requested SKUs/language.
   *
   * @param array $request
   *   Array containing SKUs and language code.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing requested data.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   *   Thrown when no log entry was provided.
   */
  public function post(array $request = []) {
    if (empty($request['skus']) || empty($request['langcode'])) {
      throw new BadRequestHttpException($this->t('Missing required parameters'));
    }

    $langcode = $this->i18nHelper->getLangcodeFromMagentoLanguage($request['langcode']);
    if (empty($langcode)) {
      throw new BadRequestHttpException($this->t('Invalid language code'));
    }

    $skus = is_array($request['skus']) ? $request['skus'] : [$request['skus']];

    $response = [];

    foreach ($skus as $sku) {
      $sku_entity = SKU::loadFromSku($sku, $langcode);

      if ($sku_entity instanceof SKUInterface) {
        /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
        $plugin = $sku_entity->getPluginInstance();
        $node = $plugin->getDisplayNode($sku_entity);
        $response[$sku]['product_url'] = $node->toUrl()->setAbsolute()->toString();

        $gallery = $this->skuImagesManager->getAllMedia($sku_entity);
        $response[$sku]['images'] = array_values(array_merge($gallery['images'], $gallery['videos']));
      }
    }

    return new ModifiedResourceResponse($response);
  }

}
