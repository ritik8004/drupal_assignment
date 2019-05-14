<?php

namespace Drupal\alshaya_kz_transac_lite;

use Drupal\Core\Url;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Session\AccountProxy;
use Drupal\node\NodeInterface;
use Drupal\node\Entity\Node;

/**
 * Class BookingPaymentManager.
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
   * The mail manager.
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
   * The BookingPaymentManager constructor.
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
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory,
                              EntityTypeManagerInterface $entity_manager,
                              MailManagerInterface $mail_manager,
                              Renderer $renderer,
                              AccountProxy $current_user) {

    $this->logger = $logger_factory->get('alshaya_kz_transac_lite');
    $this->entityManager = $entity_manager;
    $this->mailManager = $mail_manager;
    $this->renderer = $renderer;
    $this->currentUser = $current_user;
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
      $node = $this->entityManager->getStorage('node')->create([
        'type' => 'tickets',
        'title' => $booking['sales_number'],
        'field_email' => $booking['email'],
        'field_mobile_number' => $booking['mobile'],
        'field_name' => $booking['name'],
        'field_visitor_types' => $booking['visitor_types'],
        'field_visit_date' => $booking['visit_date'],
        'field_booking_date' => time(),
        'field_booking_status' => 'inactive',
        'field_payment_status' => 'pending',
        'field_payment_id' => '',
        'field_payment_type' => $booking['payment_type'],
        'field_total_price' => $booking['order_total'],
      ]);

      return $node->save($node);
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
   * @param string $sales_number
   *   Sales nunmber generated from kidsoft api.
   * @param string $payment_id
   *   Payment id generated from payment method.
   */
  public function updateTicketDetails($sales_number, $payment_id) {

    try {
      $query = \Drupal::entityQuery('node')
        ->condition('type', 'tickets')
        ->condition('title', $sales_number);
      $result = $query->execute();

      if (isset($result) && !empty($result)) {
        foreach ($result as $nid) {
          $node = Node::load($nid);
        }

        if ($node instanceof NodeInterface) {
          $node->set('field_payment_id', $payment_id);
          $node->set('field_booking_status', 'active');
          $node->set('field_payment_status', 'complete');
          $node->save();
        }
      }
    }
    catch (\Exception $e) {
      $this->logger->warning('Unable to update ticket content - %message', [
        '%message' => $e->getMessage(),
      ]);
    }

  }

  /**
   * Send booking confirmation mail to the user.
   *
   * @param array $booking_info
   *   Array of booking details.
   * @param array $final_visitor_list
   *   Array of final visitor list.
   */
  public function bookingConfirmationMail(array $booking_info, array $final_visitor_list) {
    $qr_code = [
      '#theme' => 'image',
      '#uri' => Url::fromRoute('endroid_qr_code.qr.generator', ['content' => $booking_info['sales_number']])->toString(),
      '#attributes' => ['class' => 'qr-code-image'],
    ];

    $params = [];
    $module = 'alshaya_kz_transac_lite';
    $key = 'booking_confirm';
    $to = $booking_info['email'];
    $build = [
      '#theme' => 'booking_mail',
      '#qr_code' => $qr_code,
      '#booking_info' => $booking_info,
      '#visitor_list' => $final_visitor_list,
    ];

    $body = $this->renderer->render($build);
    $params['message'] = $body;

    $params['visit_date'] = $booking_info['visit_date'];
    $params['ticket_count'] = $final_visitor_list['total']['count'];
    $params['ref_number'] = $booking_info['sales_number'];
    $langcode = $this->currentUser->getPreferredLangcode();
    $send = TRUE;
    $result = $this->mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    if ($result['result'] !== TRUE) {
      $this->logger->warning('There was a problem sending the message and it was not sent - @sales_number.', [
        '%sales_number' => $booking_info['sales_number'],
      ]);
    }
    else {
      $this->logger->warning('Message has been sent - @sales_number', [
        '%sales_number' => $booking_info['sales_number'],
      ]);
    }
  }

}
