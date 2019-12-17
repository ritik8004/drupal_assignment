<?php

namespace Drupal\alshaya_kz_transac_lite\Helper;

use Drupal\Core\TempStore\SharedTempStoreFactory;
use Drupal\Core\Url;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\alshaya_knet\Helper\KnetHelper;
use Drupal\alshaya_kz_transac_lite\BookingPaymentManager;
use Drupal\alshaya_kz_transac_lite\TicketBookingManager;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class TicketBookingKnetHelper.
 *
 * @package Drupal\alshaya_kz_transac_lite\Helper
 */
class TicketBookingKnetHelper extends KnetHelper {

  /**
   * K-Net Helper class.
   *
   * @var \Drupal\alshaya_knet\Helper\KnetHelper
   */
  protected $knetHelper;

  /**
   * The booking payment.
   *
   * @var \Drupal\alshaya_kz_transac_lite\TicketBookingManager
   */
  protected $bookingPayment;

  /**
   * The ticket booking.
   *
   * @var \Drupal\alshaya_kz_transac_lite\TicketBookingManager
   */
  protected $ticketBooking;

  /**
   * TicketBookingKnetHelper constructor.
   *
   * @param \Drupal\alshaya_knet\Helper\KnetHelper $knet_helper
   *   K-net helper.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\TempStore\SharedTempStoreFactory $temp_store_factory
   *   The factory for the temp store object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger channel.
   * @param \Drupal\alshaya_kz_transac_lite\BookingPaymentManager $booking_payment
   *   The booking payment.
   * @param \Drupal\alshaya_kz_transac_lite\TicketBookingManager $ticket_booking
   *   The ticket booking.
   */
  public function __construct(
    KnetHelper $knet_helper,
    ConfigFactoryInterface $config_factory,
    SharedTempStoreFactory $temp_store_factory,
    LoggerChannelFactoryInterface $logger_factory,
    BookingPaymentManager $booking_payment,
    TicketBookingManager $ticket_booking
  ) {
    parent::__construct($config_factory, $temp_store_factory, $logger_factory->get('alshaya_kz_transac_lite_knet'));
    $this->knetHelper = $knet_helper;
    $this->bookingPayment = $booking_payment;
    $this->ticketBooking = $ticket_booking;

  }

  /**
   * {@inheritdoc}
   */
  public function processKnetResponse(array $response = []) {
    // Get the cart using API to validate.
    $booking = $this->bookingPayment->getTicketDetails($response['quote_id']);
    if (empty($booking)) {
      throw new \Exception();
    }
    $state_key = $response['state_key'];
    $state_data = $this->tempStore->get($state_key);
    // Check if we have data in state available and it matches data in POST.
    if (empty($state_data)
      || $state_data['cart_id'] != $response['quote_id']
      || $state_data['payment_id'] != $response['payment_id']
    ) {
      $this->logger->error('KNET response data dont match data in state variable.<br>POST: @message<br>State: @state', [
        '@message' => json_encode($_POST),
        '@state' => json_encode($state_data),
      ]);
      throw new \Exception();
    }
    if ($state_data['amount'] != $booking['order_total']) {
      $this->logger->error('Currently, amount dont match amount in state variable.<br>POST: @message<br>State: @state', [
        '@message' => json_encode($_POST),
        '@state' => json_encode($state_data),
      ]);
      throw new \Exception();
    }
    // Store amount in state variable for logs.
    $response['amount'] = $booking['order_total'];
    $this->tempStore->set($state_key, $response);
    $url_options = [
      'https' => TRUE,
      'absolute' => TRUE,
    ];
    $result_url = 'REDIRECT=';
    if ($response['result'] == 'CAPTURED') {
      $route = 'alshaya_knet.success';
    }
    else {
      $route = 'alshaya_knet.failed';
    }

    $redirect_url = Url::fromRoute($route, ['state_key' => $state_key], $url_options)->toString();
    $result_url .= $redirect_url;

    $this->logger->info('KNET update for Response: @message State: @state', [
      '@message' => json_encode($response),
      '@state' => $state_data,
    ]);

    // For new K-Net toolkit, we need to redirect.
    if ($this->knetHelper->useNewKnetToolKit()) {
      return new RedirectResponse($redirect_url, 302);
    }
    else {
      print $result_url;
      exit;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function processKnetSuccess(string $state_key, array $data = []) {
    if ($data['result'] !== 'CAPTURED') {
      return $this->processKnetFailed($state_key);
    }
    // Activate order and notify the user via mail
    // and sms about ticket booking.
    if (!empty($data['transaction_id'])
      && $this->ticketBooking->payOrder($data['quote_id'], $data['amount'], $data['transaction_id'])
      && $this->ticketBooking->activateOrder($data['quote_id'])
    ) {
      $this->bookingPayment->updateTicketDetails($data, 1);
      $booking_info = $this->bookingPayment->getTicketDetails($data['quote_id']);
      $this->bookingPayment->bookingConfirmationMail($booking_info);
      // @todo - send sms.
    }
    else {
      return $this->processKnetFailed($state_key);
    }
    $this->logger->info('KNET payment complete for @quote_id.<br>@message', [
      '@quote_id' => $data['quote_id'],
      '@message' => json_encode($data),
    ]);
    $url = Url::fromRoute('alshaya_kz_transac_lite.payemnt_status', ['ref_number' => $data['quote_id']])->toString();
    return new RedirectResponse($url);
  }

  /**
   * {@inheritdoc}
   */
  public function processKnetFailed(string $state_key) {
    $data = $this->tempStore->get($state_key);
    parent::processKnetFailed($state_key);
    // Update current ticket details with payment id and transaction id.
    $this->bookingPayment->updateTicketDetails($data);
    $this->logger->error('Sorry, we are unable to process your payment. Please contact our customer service team for assistance.</br> Transaction ID: @transaction_id Payment ID: @payment_id Result code: @result_code', [
      '@transaction_id' => !empty($data['transaction_id']) ? $data['transaction_id'] : '',
      '@payment_id' => $data['payment_id'],
      '@result_code' => $data['result'],
    ]);
    $url = Url::fromRoute('alshaya_kz_transac_lite.payemnt_status', ['ref_number' => $data['quote_id']])->toString();
    return new RedirectResponse($url, 302);
  }

  /**
   * {@inheritdoc}
   */
  public function processKnetError(string $quote_id) {
    $message = $this->t('User either cancelled or response url returned error.');
    $message .= PHP_EOL . $this->t('Debug info:') . PHP_EOL;
    foreach ($_GET as $key => $value) {
      $message .= $key . ': ' . $value . PHP_EOL;
    }
    $this->logger->error('KNET payment failed for @quote_id: @message', [
      '@quote_id' => $quote_id,
      '@message' => $message,
    ]);
    $url = Url::fromRoute('alshaya_kz_transac_lite.payemnt_status', ['ref_number' => $quote_id])->toString();
    return new RedirectResponse($url, 302);
  }

}
