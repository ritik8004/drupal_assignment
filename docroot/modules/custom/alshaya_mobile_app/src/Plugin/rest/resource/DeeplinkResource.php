<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Path\AliasManagerInterface;

/**
 * Provides a resource to get deeplink.
 *
 * @RestResource(
 *   id = "deeplink",
 *   label = @Translation("Deeplink"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/deeplink"
 *   }
 * )
 */
class DeeplinkResource extends ResourceBase {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The path alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The mobile app utility service.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   */
  protected $mobileAppUtility;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

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
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   The mobile app utility service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              array $serializer_formats,
                              LoggerInterface $logger,
                              LanguageManagerInterface $language_manager,
                              AliasManagerInterface $alias_manager,
                              MobileAppUtility $mobile_app_utility,
                              RequestStack $request_stack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->languageManager = $language_manager;
    $this->aliasManager = $alias_manager;
    $this->mobileAppUtility = $mobile_app_utility;
    $this->requestStack = $request_stack->getCurrentRequest();
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
      $container->get('path.alias_manager'),
      $container->get('alshaya_mobile_app.utility'),
      $container->get('request_stack')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response returns the deeplink.
   */
  public function get() {
    // Path alias.
    $alias = $this->requestStack->query->get('url');

    if (strpos($alias, 'search') !== FALSE) {
      $all = $this->requestStack->query->all();
      $parse = parse_url($alias);
      list($key, $value) = explode('=', $parse['query']);
      $all = array_merge($all, [$key => $value]);
      unset($all['url']);
      unset($all['_format']);
      $internal_url = Url::fromUri("internal:/rest/v1/{$parse['path']}", ['query' => $all])->toString(TRUE);
      $url = $internal_url->getGeneratedUrl();
    }
    else {
      // Get the internal path of given alias and get route parameters.
      $internal_path = $this->aliasManager->getPathByAlias('/' . $alias, $this->mobileAppUtility->getAliasLang($alias));
      $url_obj = Url::fromUri("internal:" . $internal_path);
      $url = $this->mobileAppUtility->getDeepLinkFromUrl($url_obj);
    }

    return new ModifiedResourceResponse(['deeplink' => $url]);
  }

}
