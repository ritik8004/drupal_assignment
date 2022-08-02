<?php

namespace Drupal\alshaya_kz_transac_lite;

use Drupal\alshaya_kz_transac_lite\Entity\Ticket;
use Drupal\Core\Url;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Datetime\DateFormatterInterface;

/**
 * Class Booking Payment Manager.
 *
 * @package Drupal\alshaya_kz_transac_lite
 */
class BookingPaymentManager {

  /**
   * The logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Drupal\Core\Session\AccountProxy definition.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * Drupal\Core\Session\AccountProxy definition.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * BookingPaymentManager constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   Entity manager object.
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   Mail manager object.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   Render object.
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   Current user object.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory,
                              EntityTypeManagerInterface $entity_manager,
                              MailManagerInterface $mail_manager,
                              Renderer $renderer,
                              AccountProxy $current_user,
                              DateFormatterInterface $date_formatter) {

    $this->logger = $logger_factory->get('alshaya_kz_transac_lite');
    $this->entityManager = $entity_manager;
    $this->mailManager = $mail_manager;
    $this->renderer = $renderer;
    $this->currentUser = $current_user;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * Create content for Tickets type.
   *
   * @param array $booking
   *   Array of booking ticket.
   *
   * @return int
   *   Save ticket content in backend.
   */
  public function saveTicketDetails(array $booking) {
    try {
      $ticket = $this->entityManager->getStorage('ticket')->create([
        'name' => $booking['name'],
        'email' => $booking['email'],
        'telephone' => $booking['mobile']['value'],
        'payment_type' => $booking['payment_type'],
        'sales_number' => $booking['sales_number'],
        'visitor_types' => $booking['visitor_types'],
        'visit_date' => $booking['visit_date'],
        'order_total' => $booking['order_total'],
        'ticket_info' => json_encode($booking['ticket_info']),
      ]);

      return $ticket->save();
    }
    catch (\Exception $e) {
      $this->logger->warning('Unable to create ticket content - %message', [
        '%message' => $e->getMessage(),
      ]);
    }
  }

  /**
   * Update ticket details.
   *
   * @param array $knet_response
   *   Array of K-Net response.
   * @param int $flag
   *   To validate the payment status.
   */
  public function updateTicketDetails(array $knet_response, int $flag = 0) {
    try {
      $result = $this->entityManager->getStorage('ticket')->loadByProperties(['sales_number' => $knet_response['quote_id']]);
      $ticket = reset($result);
      if ($ticket instanceof Ticket) {
        $ticket->payment_id = $knet_response['payment_id'] ?? '';
        $ticket->transaction_id = $knet_response['transaction_id'] ?? '';
        if ($flag) {
          $ticket->booking_status = 'active';
          $ticket->payment_status = 'complete';
        }
        $ticket->save();
      }
    }
    catch (\Exception $e) {
      $this->logger->warning('Unable to update ticket content - %message', [
        '%message' => $e->getMessage(),
      ]);
    }
  }

  /**
   * Get ticket details.
   *
   * @param string $sales_number
   *   The sales number.
   *
   * @return array
   *   Array of booking info.
   */
  public function getTicketDetails(string $sales_number) {
    $booking_info = [];
    $ticket = $this->entityManager->getStorage('ticket')->loadByProperties(['sales_number' => $sales_number]);
    $ticket = reset($ticket);
    if ($ticket instanceof Ticket) {
      $booking_info['name'] = $ticket->get('name')->getString();
      $booking_info['email'] = $ticket->get('email')->getString();
      $booking_info['payment_type'] = $ticket->get('payment_type')->getString();
      $booking_info['sales_number'] = $ticket->get('sales_number')->getString();
      $booking_info['visitor_types'] = $ticket->get('visitor_types')->getString();
      $booking_info['visit_date'] = $ticket->get('visit_date')->getString();
      $booking_info['order_total'] = $ticket->get('order_total')->getString();
      $booking_info['booking_date'] = $this->dateFormatter->format($ticket->get('created')->getString(), '', 'Y-m-d');
      $booking_info['ticket_info'] = $ticket->get('ticket_info')->getString();
      $booking_info['payment_id'] = $ticket->get('payment_id')->getString();
      $booking_info['transaction_id'] = $ticket->get('transaction_id')->getString();
      $booking_info['payment_status'] = $ticket->get('payment_status')->getString();
    }
    return $booking_info;
  }

  /**
   * Send booking confirmation mail to the user.
   *
   * @param array $booking_info
   *   The booking details.
   */
  public function bookingConfirmationMail(array $booking_info) {
    try {
      $qr_code = [
        '#theme' => 'image',
        '#uri' => Url::fromRoute('endroid_qr_code.qr.generator', ['content' => $booking_info['sales_number']])->toString(),
        '#attributes' => ['class' => 'qr-code-image'],
      ];
      $ticket_info = json_decode($booking_info['ticket_info'], NULL);
      $ticket_count = 0;
      foreach ($ticket_info as $value) {
        $ticket_count += $value->Ticket->count;
      }
      $langcode = $this->currentUser->getPreferredLangcode();

      $params = [];
      $module = 'alshaya_kz_transac_lite';
      $key = 'booking_confirm';
      $to = $booking_info['email'];
      $build = [
        '#theme' => 'booking_mail',
        '#qr_code' => $qr_code,
        '#booking_info' => $booking_info,
        '#visitor_list' => $ticket_info,
      ];

      $body = $this->renderer->render($build);
      $params['message'] = $body;
      $params['visit_date'] = $booking_info['visit_date'];
      $params['ticket_count'] = $ticket_count;
      $params['ref_number'] = $booking_info['sales_number'];

      $result = $this->mailManager->mail($module, $key, $to, $langcode, $params, NULL, TRUE);
      if ($result['result']) {
        $this->logger->info('Message has been sent - @sales_number', [
          '@sales_number' => $booking_info['sales_number'],
        ]);
      }
    }
    catch (\Exception $e) {
      $this->logger->warning('There was a problem sending your message and it was not sent - @sales_number - @message.', [
        '@sales_number' => $booking_info['sales_number'],
        '@message' => $e->getMessage(),
      ]);
    }
  }

}
