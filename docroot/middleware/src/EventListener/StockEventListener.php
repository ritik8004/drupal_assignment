<?php

namespace App\EventListener;

use App\Service\Drupal\Drupal;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Manages stock mismatch scenarios.
 *
 * @package App\EventListener
 */
class StockEventListener {

  /**
   * One dimensional array of OOS sku values.
   *
   * @var array
   */
  public static $oosSkus = [];

  /**
   * Contains SKU and stock data.
   *
   * @var array
   */
  protected static $stockMismatchSkusData = [];

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
   * Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * StockEventListener constructor.
   *
   * @param \App\Service\Drupal\Drupal $drupal
   *   Drupal service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   RequestStack object.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   */
  public function __construct(
    Drupal $drupal,
    RequestStack $request_stack,
    LoggerInterface $logger
  ) {
    $this->drupal = $drupal;
    $this->requestStack = $request_stack;
    $this->logger = $logger;
  }

  /**
   * Sets the static array so that it can be processed later.
   *
   * @param array $skus_data
   *   Array containing arrays of sku and its quantity.
   */
  public static function matchStockQuantity(array $skus_data) {
    // Check if the array format is correct.
    foreach ($skus_data as $key => $data) {
      if (!isset($data['sku']) || !isset($data['qty'])) {
        unset($skus_data[$key]);
      }
    }
    // If empty, nothing to do.
    if (empty($skus_data)) {
      return;
    }

    foreach ($skus_data as $data) {
      self::$stockMismatchSkusData[$data['sku']] = $data['qty'];
    }
  }

  /**
   * This method is executed on kernel.terminate event.
   *
   * @param \Symfony\Component\HttpKernel\Event\TerminateEvent $event
   *   Response event.
   */
  public function onKernelTerminate(TerminateEvent $event) {
    if ((!$event->isMasterRequest())) {
      return;
    }

    if (!empty(self::$stockMismatchSkusData)) {
      $request = $event->getRequest();
      $this->requestStack->push($request);
      $this->drupal->triggerCheckoutEvent('refresh stock on deficiency', [
        'stock_mismatch_skus_data' => self::$stockMismatchSkusData,
      ]);
      $this->requestStack->pop($request);
      $this->logger->notice('Stock refresh on deficiency done for skus @skus.', [
        '@skus' => implode(',', array_keys(self::$stockMismatchSkusData)),
      ]);
    }

    if (!empty(self::$oosSkus)) {
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

}
