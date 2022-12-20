<?php

namespace Drupal\alshaya_rcs_mobile_app\EventSubscriber;

use Drupal\alshaya_acm_product_category\Event\GetEnrichedCategoryDataEvent;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds deeplink data for.
 */
class AlshayaRcsMobileAppCategoryDataAlterEventSubscriber implements EventSubscriberInterface {

  /**
   * Mobile app utility.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   */
  protected $mobileAppUtility;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The class constructor.
   *
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   Mobile app utility.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger factory service.
   */
  public function __construct(
    MobileAppUtility $mobile_app_utility,
    LoggerInterface $logger
  ) {
    $this->mobileAppUtility = $mobile_app_utility;
    $this->logger = $logger;
  }

  /**
   * Set deeplink for enriched term data.
   *
   * @param \Drupal\alshaya_acm_product_category\Event\GetEnrichedCategoryDataEvent $event
   *   The event.
   */
  public function onGetEnrichedCategoryData(GetEnrichedCategoryDataEvent $event) {
    $term_data = $event->getData();

    foreach ($term_data as $url => &$term) {
      $deeplink = NULL;
      try {
        $deeplink = $this->mobileAppUtility->getDeeplinkForResourceV3($url);
      }
      catch (\Exception $e) {
        $this->logger->info('Deeplink could not be generated for the term_id: @term_id, term url: @term_url, message: @message', [
          '@term_id' => $term['id'],
          '@term_url' => $url,
          '@message' => $e->getMessage(),
        ]);
      }
      $term['deeplink'] = $deeplink;
    }

    $event->setData($term_data);
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    return [
      GetEnrichedCategoryDataEvent::EVENT_NAME => [
        // This event should execute after alshaya_rcs_main_menu subscriber.
        ['onGetEnrichedCategoryData', 0],
      ],
    ];
  }

}
