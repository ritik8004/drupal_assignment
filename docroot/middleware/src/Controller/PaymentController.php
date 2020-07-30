<?php

namespace App\Controller;

use App\Service\Config\SystemSettings;

/**
 * Class PaymentController.
 */
class PaymentController {

  /**
   * Value to set in cookie when payment is declined.
   */
  const PAYMENT_DECLINED_VALUE = 'declined';

  /**
   * Value to set in cookie for payment or place order failure.
   */
  const PAYMENT_FAILED_VALUE = 'failed';

  /**
   * Value to set in cookie for payment or place order failure.
   */
  const PLACE_ORDER_FAILED_VALUE = 'place_order_failed';

  /**
   * Langcode used for external payments like K-Net/Checkout.com.
   *
   * @var string|null
   */
  public static $externalPaymentLangcode = NULL;

  /**
   * Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * System Settings service.
   *
   * @var \App\Service\Config\SystemSettings
   */
  protected $settings;

  /**
   * PaymentController constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Service\Config\SystemSettings $settings
   *   System Settings service.
   */
  public function __construct(LoggerInterface $logger,
                              SystemSettings $settings) {
    $this->logger = $logger;
    $this->settings = $settings;
  }

  /**
   * Get payment failed status.
   *
   * @param string|int $exception_code
   *   Exception code.
   *
   * @return string
   *   Failure status.
   */
  protected function getPaymentFailedStatus($exception_code) {
    $status = self::PAYMENT_FAILED_VALUE;

    // When backend is down and configured to show different message.
    if ($exception_code >= 600 && $this->settings->getSettings('place_order_debug_failure', 1)) {
      $status = self::PLACE_ORDER_FAILED_VALUE;
    }

    return $status;
  }

}
