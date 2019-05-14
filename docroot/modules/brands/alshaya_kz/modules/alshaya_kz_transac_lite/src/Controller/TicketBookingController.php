<?php

namespace Drupal\alshaya_kz_transac_lite\Controller;

use Drupal\Core\Url;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\alshaya_kz_transac_lite\TicketBookingManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\alshaya_kz_transac_lite\BookingPaymentManager;
use Drupal\Component\Serialization\Json;

/**
 * TicketBooking controller to fullfil the ticket booking process.
 */
class TicketBookingController extends ControllerBase {

  /**
   * The ticket booking.
   *
   * @var TicketBookingController
   */
  protected $ticketBooking;

  /**
   * The booking payment.
   *
   * @var TicketBookingController
   */
  protected $bookingPayment;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The jsonDecode.
   *
   * @var \Drupal\Component\Serialization\Json
   */
  protected $jsonDecode;

  /**
   * TicketBookingController constructor.
   *
   * @param \Drupal\alshaya_kz_transac_lite\TicketBookingManager $ticketBooking
   *   The TicketBooking object.
   * @param \Drupal\alshaya_kz_transac_lite\BookingPaymentManager $bookingPayment
   *   The Booking payemnt object.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler object.
   * @param \Drupal\Component\Serialization\Json $json_decode
   *   The serialize object to decode json into array.
   */
  public function __construct(TicketBookingManager $ticketBooking,
                              BookingPaymentManager $bookingPayment,
                              ModuleHandlerInterface $module_handler,
                              Json $json_decode) {
    $this->ticketBooking = $ticketBooking;
    $this->bookingPayment = $bookingPayment;
    $this->moduleHandler = $module_handler;
    $this->jsonDecode = $json_decode;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_kz_transac_lite.booking'),
      $container->get('alshaya_kz_transac_lite.booking_payemnt'),
      $container->get('module_handler'),
      $container->get('serialization.json')
    );
  }

  /**
   * Provide park details.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return park name as json response.
   */
  public function getParks() {
    $parks = $this->ticketBooking->getParkData();

    $response = new JsonResponse();
    $response->setData($parks->getParksResult->Park->Name);

    return $response;
  }

  /**
   * Prepare json response with shifts details.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Ajax request for visit date.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return shifts details.
   */
  public function getShifts(Request $request) {
    $visit_date = $request->request->get('visit_date');
    $shifts = $this->ticketBooking->getShiftsData($visit_date);

    $response = new JsonResponse();
    $response->setData($shifts->getShiftsResult->Shift->Name);

    return $response;
  }

  /**
   * Provide visitor details as json response.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response for visitor types.
   */
  public function getVisitorTypes() {
    $visitor_types = $this->ticketBooking->getVisitorTypesData();

    $response = new JsonResponse();
    $response->setData($visitor_types);

    return $response;
  }

  /**
   * Get sexes details as json response.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response for sex types.
   */
  public function getSexes() {
    $sexes = $this->ticketBooking->getSexesData();

    $response = new JsonResponse();
    $response->setData($sexes);

    return $response;
  }

  /**
   * Prevalidate visitor details before saving tickets.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request parameter.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   return json response.
   */
  public function validateVisitorDetails(Request $request) {
    $responseData = new \stdClass();
    $responseData->err = FALSE;

    $response = new JsonResponse();

    $final_visitor_list = $request->request->get('final_visitor_list');
    if ($this->ticketBooking->validateVisitorsList($final_visitor_list['data'])) {
      $sales_number = $this->ticketBooking->tempStore()->get('sales_number');
      if (empty($sales_number)) {
        $sales_number = $this->ticketBooking->generateSalesNumber();
      }
      $flag = FALSE;
      foreach ($final_visitor_list['data'] as $key => $value) {
        foreach ($value['Book'] as $k => $val) {
          $flag = FALSE;
          // Generate ticket number.
          $ticket_number = $this->ticketBooking->generateTicketNumber();
          $final_visitor_list['data'][$key]['Book'][$k]['ticket_id'] = $ticket_number;
          if ($this->ticketBooking->saveTicket($final_visitor_list['data'][$key], $val, $ticket_number, $sales_number)) {
            $flag = TRUE;
          }
          else {
            $responseData->err = TRUE;
            $responseData->message = $this->t('Unable to save ticket for the requested order.');
            $response->setData($responseData);
            return $response;
          }
        }
      }

      if ($flag) {
        $order_total = $this->ticketBooking->getOrderTotal($sales_number);
        if ($final_visitor_list['total']['price'] == $order_total) {
          $this->ticketBooking->tempStore()->set('final_visitor_list', json_encode($final_visitor_list));
          $response->setData($order_total);
          return $response;
        }
        $this->ticketBooking->tempStore()->delete('sales_number');
        $responseData->err = TRUE;
        $responseData->message = $this->t('Order total is not valid.');
        $response->setData($responseData);
        return $response;
      }
    }
    else {
      $responseData->err = TRUE;
      $responseData->message = $this->t('Please fill the complete form.');
      $response->setData($responseData);
      return $response;
    }
  }

  /**
   * Set the payment option and update the ticket status.
   *
   * @param string $option
   *   The option parameter.
   */
  public function paymentOption($option) {
    $booking_info = $this->jsonDecode->decode($this->ticketBooking->tempStore()->get('booking_info'));

    $payment_id = '';
    if ($option == 'knet') {
      $final_visitor_list = $this->jsonDecode->decode($this->ticketBooking->tempStore()->get('final_visitor_list'));
      // Activate order and notify the user about ticket booking.
      // update the status on order in backend.
      if ($this->ticketBooking->activateOrder($booking_info['sales_number'])) {
        $this->bookingPayment->bookingConfirmationMail($booking_info, $final_visitor_list);
        $this->bookingPayment->updateTicketDetails($booking_info['sales_number'], $payment_id);

        $path = Url::fromRoute('alshaya_kz_transac_lite.payemnt_option',
          ['option' => 'success'])->toString();
        $response = new RedirectResponse($path);
        $response->send();
      }
    }

    if ($option == 'success') {
      $build = [
        '#theme' => 'payment_success',
        '#ref_number' => $booking_info['sales_number'],
      ];

      return $build;
    }

    if ($option == 'failed') {
      $build = [
        '#theme' => 'payment_failed',
        '#ref_number' => $booking_info['sales_number'],
      ];

      return $build;
    }
  }

}
