<?php

namespace Drupal\alshaya_rcs_mobile_app\EventSubscriber;

use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Drupal\alshaya_rcs_main_menu\Event\EnrichedCategoryDataAlterEvent;
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
   * @param \Drupal\alshaya_rcs_main_menu\Event\EnrichedCategoryDataAlterEvent $event
   *   The event.
   */
  public function onEnrichedCategoryDataAlter(EnrichedCategoryDataAlterEvent $event) {
    $data = $event->getData();
    $deeplink = NULL;
    try {
      $deeplink = $this->mobileAppUtility->getDeeplinkForResource($data['term_url']);
    }
    catch (\Exception $e) {
      $this->logger->info('Deeplink could not be generated for the term_id: @term_id, term url: @term_url, message: @message', [
        '@term_id' => $data['processed_data']['id'],
        '@term_url' => $data['term_url'],
        '@message' => $e->getMessage(),
      ]);
    }
    $data['processed_data']['deeplink'] = $deeplink;
    $event->setData($data);
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

}
