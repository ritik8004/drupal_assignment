<?php

namespace Drupal\alshaya_kz_transac_lite\Helper;

use Drupal\Core\Url;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\State\StateInterface;
use Drupal\alshaya_knet\Helper\KnetHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\alshaya_kz_transac_lite\BookingPaymentManager;
use Drupal\alshaya_kz_transac_lite\TicketBookingManager;

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
   * @param \Drupal\Core\State\StateInterface $state
   *   State object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger channel.
   * @param \Drupal\alshaya_kz_transac_lite\BookingPaymentManager $booking_payment
   *   The booking payment.
   * @param \Drupal\alshaya_kz_transac_lite\TicketBookingManager $ticket_booking
   *   The ticket booking.
   */
  public function __construct(KnetHelper $knet_helper,
                              ConfigFactoryInterface $config_factory,
                              StateInterface $state,
                              LoggerChannelFactoryInterface $logger_factory,
                              BookingPaymentManager $booking_payment,
                              TicketBookingManager $ticket_booking) {
    parent::__construct($config_factory, $state, $logger_factory->get('alshaya_kz_transac_lite_knet'));
    $this->knetHelper = $knet_helper;
    $this->bookingPayment = $booking_payment;
    $this->ticketBooking = $ticket_booking;

  }

  /**
   * {@inheritdoc}
   */
  public function processKnetResponse(array $response = []) {
    $state_key = $response['state_key'];
    $state_data = $this->state->get($state_key);
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
    $result_url .= Url::fromRoute($route, ['state_key' => $state_key], $url_options)->toString();
    $this->logger->info('KNET update for Response: @message State: @state', [
      '@message' => json_encode($response),
      '@state' => json_encode($state_data),
    ]);
    print $result_url;
    exit;
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
    if ($this->ticketBooking->activateOrder($data['quote_id'])) {
      $this->bookingPayment->updateTicketDetails($data, 1);
      $booking_info = $this->bookingPayment->getTicketDetails($data['quote_id']);
      $this->bookingPayment->bookingConfirmationMail($booking_info);
      // @todo - send sms.
    }

    $this->logger->info('KNET payment complete for @quote_id.<br>@message', [
      '@quote_id' => $data['quote_id'],
      '@message' => json_encode($data),
    ]);
    $url = Url::fromRoute('alshaya_kz_transac_lite.payemnt_option', ['option' => 'success'], ['query' => ['ref_number' => $data['quote_id']]])->toString();
    return new RedirectResponse($url);
  }

  /**
   * {@inheritdoc}
   */
  public function processKnetFailed(string $state_key) {
    $data = $this->state->get($state_key);
    parent::processKnetFailed($state_key);
    // Update current ticket details with payment id and transaction id.
    $this->bookingPayment->updateTicketDetails($data);

    $this->logger->error('Sorry, we are unable to process your payment. Please contact our customer service team for assistance.</br> Transaction ID: @transaction_id Payment ID: @payment_id Result code: @result_code', [
      '@transaction_id' => !empty($data['transaction_id']) ? $data['transaction_id'] : '',
      '@payment_id' => $data['payment_id'],
      '@result_code' => $data['result'],
    ]);
    $url = Url::fromRoute('alshaya_kz_transac_lite.payemnt_option', ['option' => 'failed'], ['query' => ['ref_number' => $data['quote_id']]])->toString();
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
    $url = Url::fromRoute('alshaya_kz_transac_lite.payemnt_option', ['option' => 'failed'], ['query' => ['ref_number' => $quote_id]])->toString();
    return new RedirectResponse($url, 302);
  }

}
