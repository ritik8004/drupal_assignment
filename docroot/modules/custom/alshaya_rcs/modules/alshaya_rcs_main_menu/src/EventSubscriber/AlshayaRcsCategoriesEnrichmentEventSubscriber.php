<?php

namespace Drupal\alshaya_rcs_main_menu\EventSubscriber;

use Drupal\rest\ResourceResponse;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\alshaya_rcs_main_menu\Service\AlshayaRcsCategoryHelper;
use Drupal\alshaya_acm_product\AlshayaRequestContextManager;
use Drupal\alshaya_acm_product_category\Event\GetEnrichedCategoryDataEvent;
use Drupal\Core\Cache\CacheableMetadata;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Provides enriched rcs categories data.
 */
class AlshayaRcsCategoriesEnrichmentEventSubscriber implements EventSubscriberInterface {

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
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\alshaya_rcs_main_menu\Service\AlshayaRcsCategoryHelper $alshaya_rcs_category_helper
   *   The alshaya rcs_category helper.
   * @param \Drupal\alshaya_acm_product\AlshayaRequestContextManager $alshaya_request_context_manager
   *   Alshaya Request Context Manager.
   */
  public function __construct(
    LanguageManagerInterface $language_manager,
    AlshayaRcsCategoryHelper $alshaya_rcs_category_helper,
    AlshayaRequestContextManager $alshaya_request_context_manager
  ) {
    $this->languageManager = $language_manager;
    $this->alshayaRcsCategoryHelper = $alshaya_rcs_category_helper;
    $this->requestContextManager = $alshaya_request_context_manager;
  }

  /**
   * Subscriber for providing enriched category data.
   *
   * @param \Drupal\alshaya_acm_product_category\Event\GetEnrichedCategoryDataEvent $event
   *   The event data.
   */
  public function onGetEnrichedCategoryData(GetEnrichedCategoryDataEvent $event) {
    // Pass context for filtering a few fields.
    $context = $this->requestContextManager->getContext();

    $term_data = $this->alshayaRcsCategoryHelper
      ->getRcsCategoryEnrichmentData(
        $this->languageManager->getCurrentLanguage()->getId(),
        $context
      );

    $event->setData($term_data);
    $event->stopPropagation();
    // $this->addCacheableTermDependency($response);
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    return [
      GetEnrichedCategoryDataEvent::EVENT_NAME => [
        ['onGetEnrichedCategoryData', 2],
      ],
    ];
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
