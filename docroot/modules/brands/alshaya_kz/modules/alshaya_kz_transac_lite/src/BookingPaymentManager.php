<?php

namespace Drupal\alshaya_kz_transac_lite;

use Drupal\Core\Url;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Session\AccountProxy;

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
      $node = $this->entityManager->getStorage('ticket')->create([
        'sales_number' => $booking['sales_number'],
        'email' => $booking['email'],
        'telephone' => $booking['mobile'],
        'name' => $booking['name'],
        'visitor_types' => $booking['visitor_types'],
        'visit_date' => $booking['visit_date'],
        'booking_date' => time(),
        'booking_status' => 'inactive',
        'payment_status' => 'pending',
        'payment_type' => $booking['payment_type'],
        'payment_id' => '',
        'order_total' => $booking['order_total'],
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
  public function updateTicketDetails(string $sales_number, string $payment_id) {

    try {
      $query = $this->entityManager->getStorage('ticket')->getQuery()
        ->condition('sales_number', $sales_number);
      $result = $query->execute();

      if (isset($result) && !empty($result)) {
        foreach ($result as $id) {
          $ticket = $this->entityManager->getStorage('ticket')->load($id);
          $ticket->payment_id = $payment_id;
          $ticket->booking_status = 'active';
          $ticket->payment_status = 'complete';
          $ticket->save();
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
    try {
      $qr_code = [
        '#theme' => 'image',
        '#uri' => Url::fromRoute('endroid_qr_code.qr.generator', ['content' => $booking_info['sales_number']])->toString(),
        '#attributes' => ['class' => 'qr-code-image'],
      ];

      $langcode = $this->currentUser->getPreferredLangcode();

      // Get logo based on user preferred language.
      $email_logo = alshaya_master_get_email_logo(NULL, $langcode);
      if (empty($email_logo['logo_url'])) {
        $email_logo['logo_url'] = file_create_url($email_logo['logo_path']);
      }

      $params = [];
      $module = 'alshaya_kz_transac_lite';
      $key = 'booking_confirm';
      $to = $booking_info['email'];
      $build = [
        '#theme' => 'booking_mail',
        '#qr_code' => $qr_code,
        '#booking_info' => $booking_info,
        '#visitor_list' => $final_visitor_list,
        '#email_logo' => $email_logo['logo_url'],
      ];

      $body = $this->renderer->render($build);
      $params['message'] = $body;
      $params['visit_date'] = $booking_info['visit_date'];
      $params['ticket_count'] = $final_visitor_list['total']['count'];
      $params['ref_number'] = $booking_info['sales_number'];

      $send = TRUE;
      $result = $this->mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
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
