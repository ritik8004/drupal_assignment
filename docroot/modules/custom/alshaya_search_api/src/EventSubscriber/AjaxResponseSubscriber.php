<?php

namespace Drupal\alshaya_search_api\EventSubscriber;

use Drupal\alshaya_search_api\AlshayaSearchApiHelper;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\views\Ajax\ViewAjaxResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Response subscriber to handle AJAX responses.
 */
class AjaxResponseSubscriber implements EventSubscriberInterface {

  /**
   * Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Helper service.
   *
   * @var \Drupal\alshaya_search_api\AlshayaSearchApiHelper
   */
  protected $helper;

  /**
   * Constructs a new AjaxResponseSubscriber object.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language Manager.
   * @param \Drupal\alshaya_search_api\AlshayaSearchApiHelper $helper
   *   Helper service.
   */
  public function __construct(LanguageManagerInterface $language_manager,
                              AlshayaSearchApiHelper $helper) {
    $this->languageManager = $language_manager;
    $this->helper = $helper;
  }

  /**
   * Renders the ajax commands right before preparing the result.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The response event, which contains the possible AjaxResponse object.
   */
  public function onResponse(FilterResponseEvent $event) {
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

    $query_params = $this->helper->getCleanQueryParams($view->getExposedInput());

    // Update the link for language switcher.
    foreach ($this->languageManager->getLanguages() as $language) {
      if ($language->getId() === $this->languageManager->getCurrentLanguage()->getId()) {
        continue;
      }

      $language_switcher_query = http_build_query($this->helper->getParamsInOtherLanguage($language->getId(), $query_params));
      $response->addCommand(new InvokeCommand(NULL, 'updateLanguageSwitcherLinkQuery', [$language->getId(), $language_switcher_query]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::RESPONSE => [['onResponse']]];
  }

}
