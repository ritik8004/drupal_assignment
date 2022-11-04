<?php

namespace Drupal\alshaya_mobile_app\EventSubscriber;

use Drupal\alshaya_acm_product_category\Event\EnrichedCategoryDataAlterEvent;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Alter the categories data.
 */
class CategoriesEnrichmentEventSubscriber implements EventSubscriberInterface {

  /**
   * Mobile app utility.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   *   Mobile app utility.
   */
  protected $mobileAppUtility;

  /**
   * Constructor for CategoriesEnrichmentEventSubscriber.
   *
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   Mobile app utility.
   */
  public function __construct(MobileAppUtility $mobile_app_utility) {
    $this->mobileAppUtility = $mobile_app_utility;
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    return [
      EnrichedCategoryDataAlterEvent::EVENT_NAME => [
        ['onEnrichedCategoryDataAlter'],
      ],
    ];
  }

  /**
   * Alter the term data in the event.
   *
   * @param \Drupal\alshaya_acm_product_category\Event\EnrichedCategoryDataAlterEvent $event
   *   Contains term data to alter.
   */
  public function onEnrichedCategoryDataAlter(EnrichedCategoryDataAlterEvent $event) {
    $term_data = $event->getData();
    $term_data['processed_data']['deeplink'] = $this->mobileAppUtility->getDeepLink($term_data['term']);
    $event->setData($term_data);
  }

}
