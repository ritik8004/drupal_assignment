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
use Drupal\path_alias\AliasManagerInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Routing\RequestContext;

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
   * @var \Drupal\path_alias\AliasManagerInterface
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
   * Base url of current site.
   *
   * @var string
   */
  protected $baseUrl;

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
    RequestContext $request_context
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->languageManager = $language_manager;
    $this->aliasManager = $alias_manager;
    $this->mobileAppUtility = $mobile_app_utility;
    $this->requestStack = $request_stack->getCurrentRequest();
    $this->baseUrl = $request_context->getCompleteBaseUrl();
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
      $container->get('router.request_context')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response returns the deeplink.
   */
  public function get() {
    $alias = $this->requestStack->query->get('url');
    $url = $this->getDeeplink($alias);
    return new ModifiedResourceResponse(['deeplink' => $url]);
  }

  /**
   * Helper function to get deeplink.
   */
  protected function getDeeplink($alias) {
    $alias = str_replace($this->baseUrl, '', $alias);

    if (empty($alias) || UrlHelper::isExternal($alias)) {
      return $this->mobileAppUtility->throwException();
    }

    if (str_contains($alias, 'search')) {
      $query_string_array = $this->requestStack->query->all();
      // Search url may have url like,
      // rest/v1/deeplink?url=search?keywords=dress&f[0]=category
      // %3A10711&sort_bef_combine=search_api_relevance DESC&show_on_load=12
      // So, the $alias contains query string like search?keywords=dress
      // Which further needs to be parsed and "keywords" needs to be added
      // back to query string array to generate complete search deep link.
      $parse = parse_url($alias);
      [$key, $value] = explode('=', $parse['query']);
      $query_string_array = array_merge($query_string_array, [$key => $value]);
      unset($query_string_array['url']);
      unset($query_string_array['_format']);
      $internal_url = Url::fromUri("internal:/rest/v1/{$parse['path']}", ['query' => $query_string_array])->toString(TRUE);
      $url = $internal_url->getGeneratedUrl();
    }
    else {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
      $internal_path = $this->aliasManager->getPathByAlias(
        rtrim(str_replace("/{$langcode}", '', $alias), '/'),
        $langcode
      );
      if (strpos($internal_path, 'taxonomy/term')) {
        $redirect_url = $this->mobileAppUtility->getRedirectUrl("/{$langcode}" . $internal_path);
        // Append '/' if it does not exist.
        $redirect_url = (!str_starts_with($redirect_url, '/')) ? ('/' . $redirect_url) : $redirect_url;
        if ($redirect_url !== $internal_path) {
          $internal_path = $this->aliasManager->getPathByAlias(
            rtrim(str_replace("/{$langcode}", '', $redirect_url), '/'),
            $langcode
          );
        }
      }
      else {
        $redirect_url = $this->mobileAppUtility->getRedirectUrl($alias);
        // Get the internal path of given alias and get route object.
        // If $redirect_url is "/" or "/?xyz" we dont find its path.
        $internal_path = (preg_match('/^\/(\?.*)?$/', $redirect_url))
          ? $redirect_url
          : $this->aliasManager->getPathByAlias('/' . $redirect_url, $langcode);
      }

      // Get the internal path of given alias and get route object.
      $url_obj = Url::fromUri("internal:" . $internal_path);
      if (!$url_obj->isRouted()) {
        return $this->mobileAppUtility->throwException();
      }
      $url = $this->mobileAppUtility->getDeepLinkFromUrl($url_obj);
    }

    return $url;
  }

}
