<?php

namespace Drupal\alshaya_acm\EventSubscriber;

use Drupal\alshaya_acm\AlshayaAcmConfigCheck;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to the kernel request event to check env specific config.
 */
class AlshayaAcmSubscriber implements EventSubscriberInterface {

  /**
   * ACM Config check service object.
   *
   * @var \Drupal\alshaya_acm\AlshayaAcmConfigCheck
   */
  protected $acmConfigCheck;

  /**
   * AlshayaAcmSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm\AlshayaAcmConfigCheck $acm_config_check
   *   ACM Config check service object.
   */
  public function __construct(AlshayaAcmConfigCheck $acm_config_check) {
    $this->acmConfigCheck = $acm_config_check;
  }

  /**
   * Check environment specific config on each request.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event to process.
   */
  public function checkAcmConfig(GetResponseEvent $event) {
    $this->acmConfigCheck->checkConfig();
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[KernelEvents::REQUEST][] = ['checkAcmConfig'];
    return $events;
  }

}
