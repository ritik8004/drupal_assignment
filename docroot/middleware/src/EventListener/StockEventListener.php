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
   * @param string $sku
   *   The SKU value.
   * @param int $quantity
   *   The quantity of the SKU.
   */
  public static function matchStockQuantity(string $sku, int $quantity = 0) {
    self::$stockMismatchSkusData[$sku] = $quantity;
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
      $this->drupal->triggerCheckoutEvent('refresh stock', [
        'skus_quantity' => self::$stockMismatchSkusData,
      ]);
      $this->requestStack->pop($request);
      $this->logger->notice('Stock refresh process is completed for the following skus with requested quantity: @skus.', [
        '@skus' => json_encode(self::$stockMismatchSkusData),
      ]);
    }
  }

}
