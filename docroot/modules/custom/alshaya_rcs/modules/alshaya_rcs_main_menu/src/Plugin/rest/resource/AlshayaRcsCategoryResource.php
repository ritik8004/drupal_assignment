<?php

namespace Drupal\alshaya_rcs_main_menu\Plugin\rest\resource;

use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\alshaya_rcs_main_menu\Service\AlshayaRcsCategoryHelper;
use Drupal\alshaya_acm_product\AlshayaRequestContextManager;
use Drupal\Core\Cache\CacheableMetadata;

/**
 * Provides a resource to get list of all categories.
 *
 * @RestResource(
 *   id = "rcscategories",
 *   label = @Translation("List all rcs categories with enrichment data"),
 *   uri_paths = {
 *     "canonical" = "/rest/v2/categories"
 *   }
 * )
 */
class AlshayaRcsCategoryResource extends ResourceBase {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The alshaya rcs_category helper.
   *
   * @var Drupal\alshaya_rcs_main_menu\Service\AlshayaRcsCategoryHelper
   */
  protected $alshayaRcsCategoryHelper;

  /**
   * Alshaya Request Context Manager.
   *
   * @var \Drupal\alshaya_acm_product\AlshayaRequestContextManager
   */
  protected $requestContextManager;

  /**
   * AlshayaRcsCategoryResource constructor.
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
   * @param \Drupal\alshaya_rcs_main_menu\Service\AlshayaRcsCategoryHelper $alshaya_rcs_category_helper
   *   The alshaya rcs_category helper.
   * @param \Drupal\alshaya_acm_product\AlshayaRequestContextManager $alshaya_request_context_manager
   *   Alshaya Request Context Manager.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              array $serializer_formats,
                              LoggerInterface $logger,
                              LanguageManagerInterface $language_manager,
                              AlshayaRcsCategoryHelper $alshaya_rcs_category_helper,
                              AlshayaRequestContextManager $alshaya_request_context_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->languageManager = $language_manager;
    $this->alshayaRcsCategoryHelper = $alshaya_rcs_category_helper;
    $this->requestContextManager = $alshaya_request_context_manager;
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
      $container->get('alshaya_rcs_main_menu.rcs_category_helper'),
      $container->get('alshaya_acm_product.context_manager')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing list of categories.
   */
  public function get() {
    // Pass context for filtering a few fields.
    $context = $this->requestContextManager->getContext();

    $response_data = $this->alshayaRcsCategoryHelper
      ->getRcsCategoryEnrichmentData(
        $this->languageManager->getCurrentLanguage()->getId(),
        $context
      );

    $response = new ResourceResponse($response_data);
    $this->addCacheableTermDependency($response);
    return $response;
  }

  /**
   * Adding rcs category terms dependency to response.
   *
   * @param \Drupal\rest\ResourceResponse $response
   *   Response object.
   */
  protected function addCacheableTermDependency(ResourceResponse $response) {
    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
      '#cache' => [
        'tags' => $this->alshayaRcsCategoryHelper->getTermsCacheTags(),
      ],
    ]));
  }

}
