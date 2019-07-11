<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Drupal\redirect\RedirectRepository;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Path\AliasManagerInterface;
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
   * Base url of current site.
   *
   * @var string
   */
  protected $baseUrl;

  /**
   * Redirect repository.
   *
   * @var \Drupal\redirect\RedirectRepository
   */
  protected $redirectRepository;

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
   * @param \Drupal\Core\Routing\RequestContext $request_context
   *   The request context.
   * @param \Drupal\redirect\RedirectRepository $redirect_repository
   *   Redirect repository.
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
    RedirectRepository $redirect_repository
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->languageManager = $language_manager;
    $this->aliasManager = $alias_manager;
    $this->mobileAppUtility = $mobile_app_utility;
    $this->requestStack = $request_stack->getCurrentRequest();
    $this->baseUrl = $request_context->getCompleteBaseUrl();
    $this->redirectRepository = $redirect_repository;
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
      $container->get('request_stack'),
      $container->get('router.request_context'),
      $container->get('redirect.repository')
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
    $alias = str_replace($this->baseUrl, '', $alias);

    if (empty($alias) || UrlHelper::isExternal($alias)) {
      return $this->mobileAppUtility->throwException();
    }

    if (strpos($alias, 'search') !== FALSE) {
      $query_string_array = $this->requestStack->query->all();
      // Search url may have url like,
      // rest/v1/deeplink?url=search?keywords=dress&f[0]=category
      // %3A10711&sort_bef_combine=search_api_relevance DESC&show_on_load=12
      // So, the $alias contains query string like search?keywords=dress
      // Which further needs to be parsed and "keywords" needs to be added
      // back to query string array to generate complete search deep link.
      $parse = parse_url($alias);
      list($key, $value) = explode('=', $parse['query']);
      $query_string_array = array_merge($query_string_array, [$key => $value]);
      unset($query_string_array['url']);
      unset($query_string_array['_format']);
      $internal_url = Url::fromUri("internal:/rest/v1/{$parse['path']}", ['query' => $query_string_array])->toString(TRUE);
      $url = $internal_url->getGeneratedUrl();
    }
    else {
      $alias = $this->getRedirectUrl($alias);
      // Get the internal path of given alias and get route object.
      $internal_path = $this->aliasManager->getPathByAlias(
        '/' . $alias,
        $this->languageManager->getCurrentLanguage()->getId()
      );
      $url_obj = Url::fromUri("internal:" . $internal_path);
      if (!$url_obj->isRouted()) {
        return $this->mobileAppUtility->throwException();
      }
      $url = $this->mobileAppUtility->getDeepLinkFromUrl($url_obj);
    }

    return new ModifiedResourceResponse(['deeplink' => $url]);
  }

  /**
   * Get redirect url for a given url.
   *
   * @param string $url
   *   Url for which redirect needs to check.
   *
   * @return mixed|string
   *   Redirect url.
   */
  protected function getRedirectUrl(string $url = '') {
    if (empty($url)) {
      return $url;
    }

    preg_match('#(?<=/)[^/]+#', $url, $match);
    $langcode = NULL;

    // If language exists for the given langcode.
    if (!empty($match) && $this->languageManager->getLanguage($match[0])) {
      $langcode = $match[0];
    }

    // If langcode exists in the url string.
    if ($langcode && strpos($url, '/' . $langcode . '/') !== FALSE) {
      $url = str_replace('/' . $langcode . '/', '', $url);
      $redirect = $this->redirectRepository->findMatchingRedirect($url, [], $langcode);
      $url = $redirect
        ? $redirect->getRedirectUrl()->toString(TRUE)->getGeneratedUrl()
        : $url;
    }

    return $url;
  }

}
