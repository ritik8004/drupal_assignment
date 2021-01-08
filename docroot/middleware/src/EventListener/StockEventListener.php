<?php

namespace App\EventListener;

use App\Service\Drupal\Drupal;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Manages OOS scenarios.
 *
 * @package App\EventListener
 */
class StockEventListener {

  /**
   * One dimensional array of OOS sku values.
   *
   * @var bool
   */
  public static $oosSkus = [];

  /**
   * Drupal service.
   *
   * @var \App\Service\Drupal\Drupal
   */
  protected $drupal;

  /**
   * RequestStack Object.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * StockEventListener constructor.
   *
   * @param \App\Service\Drupal\Drupal $drupal
   *   Drupal service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   RequestStack object.
   */
  public function __construct(
    Drupal $drupal,
    RequestStack $request_stack
  ) {
    $this->drupal = $drupal;
    $this->requestStack = $request_stack;
  }

  /**
   * This method is executed on kernel.terminate event.
   *
   * @param \Symfony\Component\HttpKernel\Event\TerminateEvent $event
   *   Response event.
   */
  public function onKernelTerminate(TerminateEvent $event) {
    if ((!$event->isMasterRequest()) || empty(self::$oosSkus)) {
      return;
    }

    // OOS products have been detected. So refresh stock for them.
    // When we trigger the checkout event, we do not get the request object by
    // default. So we set the current request object here so that it can be
    // utilized there to fetch cookies etc.
    $request = $event->getRequest();
    $this->requestStack->push($request);
    $this->drupal->triggerCheckoutEvent('refresh stock', [
      'skus' => self::$oosSkus,
    ]);
    $this->requestStack->pop($request);
  }

}
