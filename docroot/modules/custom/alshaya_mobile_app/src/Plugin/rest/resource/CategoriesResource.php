<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;

/**
 * Provides a resource to get list of all categories.
 *
 * @RestResource(
 *   id = "categories",
 *   label = @Translation("List all categories"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/category/all"
 *   }
 * )
 */
class CategoriesResource extends ResourceBase {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The mobile app utility service.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   */
  protected $mobileAppUtility;

  /**
   * AlshayaErrorMessages constructor.
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
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   The mobile app utility service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              array $serializer_formats,
                              LoggerInterface $logger,
                              LanguageManagerInterface $language_manager,
                              MobileAppUtility $mobile_app_utility) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->languageManager = $language_manager;
    $this->mobileAppUtility = $mobile_app_utility;
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
      $container->get('language_manager'),
      $container->get('alshaya_mobile_app.utility')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing list of categories.
   */
  public function get() {
    $response_data = $this->mobileAppUtility->getAllCategories($this->languageManager->getCurrentLanguage()->getId());
    $response = new ResourceResponse($response_data);
    $this->addCacheableDependency($response);
    return $response;
  }

  /**
   * Adding content dependency to the response.
   *
   * @param \Drupal\rest\ResourceResponse $response
   *   Response object.
   */
  protected function addCacheableDependency(ResourceResponse $response) {
    if (!empty($this->mobileAppUtility->cacheableTermUrls())) {
      foreach ($this->mobileAppUtility->cacheableTermUrls() as $urls) {
        $response->addCacheableDependency($urls);
      }
    }

    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
      '#cache' => [
        'tags' => array_merge($this->mobileAppUtility->cacheableTermTags(), [ProductCategoryTree::CACHE_TAG]),
      ],
    ]));
  }

}
