<?php

namespace Drupal\alshaya_search\EventSubscriber;

use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Drupal\views\Ajax\ViewAjaxResponse;
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
   * Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new AjaxResponseSubscriber object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Symfony\Component\HttpFoundation\RequestStack definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Lanaguage Manager.
   */
  public function __construct(RequestStack $request_stack,
                              ConfigFactoryInterface $config_factory,
                              LanguageManagerInterface $language_manager) {
    $this->requestStack = $request_stack;
    $this->backToListEnabled = (bool) $config_factory->get('alshaya_acm_product.settings')->get('back_to_list');
    $this->languageManager = $language_manager;
  }

  /**
   * Renders the ajax commands right before preparing the result.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The response event, which contains the possible AjaxResponse object.
   */
  public function onResponse(FilterResponseEvent $event) {
    // Do nothing if back to list is disabled.
    if (!$this->backToListEnabled) {
      return;
    }

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

    if ($view->storage->id() === 'search') {
      $view_url = '/' . $view->getPath();
    }
    else {
      $request = $this->requestStack->getCurrentRequest();
      $query_string = [];
      parse_str($request->getQueryString(), $query_string);
      $view_url_data = $query_string['view_path'];
      $view_url_data = str_replace('/' . $this->languageManager->getCurrentLanguage()->getId(), '', $view_url_data);
      $view_url_data = explode('?', $view_url_data);
      $view_url = reset($view_url_data);
    }

    $query_params = $view->getExposedInput();

    // Cleanup query params.
    unset($query_params['pager_query_method']);

    $url = Url::fromUserInput($view_url, [
      'query' => $query_params,
    ])->toString(FALSE);

    $response->addCommand(new InvokeCommand(NULL, 'updateWindowLocation', [$url]));

    // Update the link for language switcher.
    foreach ($this->languageManager->getLanguages() as $language) {
      if ($language->getId() === $this->languageManager->getCurrentLanguage()->getId()) {
        continue;
      }

      // For Alshaya we know we will have only two languages.
      // This code will need to be updated as soon as we start using third
      // language.
      $language_switcher_url = Url::fromUserInput('/' . $view->getPath(), [
        'query' => $query_params,
        'language' => $language,
      ])->toString(FALSE);
    }

    $response->addCommand(new InvokeCommand(NULL, 'updateLanguageSwitcherLink', [$language_switcher_url]));
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::RESPONSE => [['onResponse']]];
  }

}
