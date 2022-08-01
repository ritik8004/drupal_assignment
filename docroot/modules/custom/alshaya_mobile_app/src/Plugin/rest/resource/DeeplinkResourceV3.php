<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\Core\Routing\RequestContext;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Provides a resource to get deeplink in v3.
 *
 * @RestResource(
 *   id = "deeplink_v3",
 *   label = @Translation("Deeplink V3"),
 *   uri_paths = {
 *     "canonical" = "/rest/v3/deeplink"
 *   }
 * )
 */
class DeeplinkResourceV3 extends DeeplinkResource {

  /**
   * Contains the configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The path validator service.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * DeeplinkResource constructor.
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
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   The mobile app utility service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\Core\Routing\RequestContext $request_context
   *   The request context.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The configuration factory service.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    LanguageManagerInterface $language_manager,
    AliasManagerInterface $alias_manager,
    MobileAppUtility $mobile_app_utility,
    RequestStack $request_stack,
    RequestContext $request_context,
    ConfigFactoryInterface $config,
    PathValidatorInterface $path_validator,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger, $language_manager, $alias_manager, $mobile_app_utility, $request_stack, $request_context, $entity_type_manager);
    $this->configFactory = $config;
    $this->pathValidator = $path_validator;
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('path_alias.manager'),
      $container->get('alshaya_mobile_app.utility'),
      $container->get('request_stack'),
      $container->get('router.request_context'),
      $container->get('config.factory'),
      $container->get('path.validator'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The response returns the deeplink.
   */
  public function get() {
    $alias = $this->requestStack->query->get('url');
    $response = $this->getDeeplink($alias);
    return new ModifiedResourceResponse($response);
  }

  /**
   * Helper function to get deeplink.
   *
   * @param string $alias
   *   Url alias.
   *
   * @return array
   *   Returns V3 deeplink response.
   */
  protected function getDeeplink($alias) {
    // Check if its mdc url.
    if ($this->checkMdcUrl($alias)) {
      return [
        'deeplink' => '',
        'source' => 'magento',
      ];
    }

    $url = parent::getDeeplink($alias);
    return [
      'deeplink' => $url,
      'source' => 'drupal',
    ];
  }

  /**
   * Preprocess the alias.
   *
   * @param string $alias
   *   Path alias.
   */
  private function preprocessAlias(&$alias) {
    // Remove the base url from the alias.
    $alias = str_replace($this->baseUrl, '', $alias);
    // Append .html in the end if it is a product url without .html.
    if (str_contains($alias, 'buy-') && !str_contains($alias, '.html')) {
      $alias = "$alias.html";
    }
  }

  /**
   * Check if its MDC url.
   *
   * @param string $alias
   *   Url alias.
   *
   * @return bool
   *   Returns true if its MDC url.
   */
  protected function checkMdcUrl($alias) {
    $this->preprocessAlias($alias);

    if (empty($alias) || UrlHelper::isExternal($alias)) {
      return $this->mobileAppUtility->throwException();
    }
    // Get route name for the url.
    $url_object = $this->pathValidator->getUrlIfValid($alias);
    if ($url_object === FALSE) {
      return FALSE;
    }

    $route_name = $url_object->getRouteName();
    $route_parameters = $url_object->getRouteParameters();
    // Check if its PLP route.
    if ($route_name == 'entity.taxonomy_term.canonical') {
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($route_parameters['taxonomy_term']);
      if ($term instanceof TermInterface
        && in_array($term->bundle(), ['acq_product_category', 'rcs_category'])
      ) {
        return TRUE;
      }
    }
    elseif ($route_name == 'entity.node.canonical') {
      // Check if its PDP route.
      $node = $this->entityTypeManager->getStorage('node')->load($route_parameters['node']);
      if ($node instanceof NodeInterface
        && in_array($node->bundle(), [
          'acq_product',
          'rcs_product',
          'acq_promotion',
          'rcs_promotion',
        ])
      ) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
