<?php

namespace Drupal\alshaya_search_api\EventSubscriber;

use Drupal\alshaya_search_api\AlshayaSearchApiHelper;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Drupal\views\Ajax\ViewAjaxResponse;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Response subscriber to handle AJAX responses.
 */
class AjaxResponseSubscriber implements EventSubscriberInterface {

  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Flag to specific if back to list is enabled or not from config.
   *
   * @var bool
   */
  protected $backToListEnabled;

  /**
   * Helper service.
   *
   * @var \Drupal\alshaya_search_api\AlshayaSearchApiHelper
   */
  protected $helper;

  /**
   * Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Constructs a new AjaxResponseSubscriber object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Symfony\Component\HttpFoundation\RequestStack definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language Manager.
   * @param \Drupal\alshaya_search_api\AlshayaSearchApiHelper $helper
   *   Helper service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(RequestStack $request_stack,
                              ConfigFactoryInterface $config_factory,
                              LanguageManagerInterface $language_manager,
                              AlshayaSearchApiHelper $helper,
                              LoggerChannelFactoryInterface $logger_factory) {
    $this->requestStack = $request_stack;
    $this->config = $config_factory;
    $this->backToListEnabled = (bool) $config_factory->get('alshaya_acm_product.settings')->get('back_to_list');
    $this->languageManager = $language_manager;
    $this->helper = $helper;
    $this->logger = $logger_factory->get('alshaya_search_api');
  }

  /**
   * Renders the ajax commands right before preparing the result.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The response event, which contains the possible AjaxResponse object.
   */
  public function onResponse(FilterResponseEvent $event) {
    $query_string = [];
    // Do nothing if back to list is disabled.
    if (!$this->backToListEnabled) {
      return;
    }

    $url_processor = '';

    $response = $event->getResponse();

    // Process only for views ajax responses.
    if (!($response instanceof ViewAjaxResponse)) {
      return;
    }

    $view = $response->getView();

    // Process only for listing views.
    $views_to_alter = [
      'alshaya_product_list',
      'search',
    ];

    if (!in_array($view->storage->id(), $views_to_alter)) {
      return;
    }

    $request = $this->requestStack->getCurrentRequest();

    if ($view->storage->id() === 'search') {
      $view_url = '/' . $view->getPath();
      $url_processor = $this->config->get('search_api__views_page__search__page')->get('url_processor');
    }
    else {
      $url_processor = $this->config->get('facets.facet_source.search_api__views_block__alshaya_product_list__' . $view->current_display)->get('url_processor');
      $query_string = [];
      parse_str($request->getQueryString(), $query_string);

      // Query string must have view_path.
      if (empty($query_string['view_path'])) {
        // Add the log.
        $this->logger->notice('Key view_path key is not available in query string for view:@view query_string:@query_string', [
          '@view' => $view->storage->id(),
          '@query_string' => $request->getQueryString(),
        ]);
        return;
      }

      $view_url_data = $query_string['view_path'];
      $view_url_data = preg_replace('/\/' . $this->languageManager->getCurrentLanguage()->getId() . '/', '', $view_url_data, 1);
      $view_url_data = explode('?', $view_url_data);
      $view_url = reset($view_url_data);
    }

    $query_params = $this->helper->getCleanQueryParams($view->getExposedInput());

    $pretty_filters = '';

    if ($url_processor == 'alshaya_facets_pretty_paths') {
      $view_url = Url::fromUserInput($view_url, [])->toString(FALSE);
      if (isset($query_string['q'])) {
        $view_url = Url::fromUserInput($query_string['q'], [])
          ->toString(FALSE);
      }
      elseif (isset($query_params['facet_filter_url'])) {
        $view_url = Url::fromUserInput($query_params['facet_filter_url'], [])
          ->toString(FALSE);
      }
      $view_url .= (substr($view_url, -1) == '/' ? '' : '/');
      $response->addCommand(new InvokeCommand(NULL, 'updateBrowserFacetUrl', [urldecode($view_url)]));

      if (str_contains($view_url, "/--")) {
        $pretty_filters = substr($view_url, strpos($view_url, "/--"));
      }
    }

    // Update the link for language switcher.
    foreach ($this->languageManager->getLanguages() as $language) {
      if ($language->getId() === $this->languageManager->getCurrentLanguage()->getId()) {
        continue;
      }

      $language_switcher_query = http_build_query($this->helper->getParamsInOtherLanguage($language->getId(), $query_params));
      $response->addCommand(new InvokeCommand(NULL, 'updateLanguageSwitcherLinkQuery', [
        $language->getId(),
        $language_switcher_query,
        $pretty_filters,
      ]));
    }

    // Set items per page to current page * items per page.
    $currentPage = intval($request->query->get('page'));
    $query_params['show_on_load'] = ($currentPage + 1) * _alshaya_acm_product_get_items_per_page_on_listing();

    unset($query_params['facet_filter_url']);
    unset($query_params['view_raw_path']);
    unset($query_params['view_path']);

    $url = Url::fromUserInput($view_url, [
      'query' => $query_params,
    ])->toString(FALSE);

    $response->addCommand(new InvokeCommand(NULL, 'updateWindowLocation', [$url]));
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::RESPONSE => [['onResponse']]];
  }

}
