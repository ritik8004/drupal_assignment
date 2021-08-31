<?php

namespace Drupal\alshaya_kz_transac_lite\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\alshaya_kz_transac_lite\BookingPaymentManager;
use Drupal\alshaya_kz_transac_lite\TicketBookingManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * TicketBookingController constructor.
   *
   * @param \Drupal\alshaya_kz_transac_lite\TicketBookingManager $ticket_booking
   *   The TicketBooking object.
   * @param \Drupal\alshaya_kz_transac_lite\BookingPaymentManager $booking_payment
   *   The Booking payment object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory object.
   */
  public function __construct(TicketBookingManager $ticket_booking,
                              BookingPaymentManager $booking_payment,
                              ConfigFactoryInterface $config_factory) {
    $this->ticketBooking = $ticket_booking;
    $this->bookingPayment = $booking_payment;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_kz_transac_lite.booking_manager'),
      $container->get('alshaya_kz_transac_lite.booking_payment_manager'),
      $container->get('config.factory')
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

    $response = new CacheableJsonResponse($parks, 200);

    // Adding cacheability metadata, so whenever, cache invalidates, this
    // url's cached response also gets invalidate.
    $cacheMeta = new CacheableMetadata();

    // Adding cache tags.
    $cacheMeta->addCacheTags(['booking_steps:getParks']);
    $response->addCacheableDependency($cacheMeta);

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
    $response->setData($shifts);

    return $response;
  }

  /**
   * Provide visitor details as json response.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request parameters for visit date and shifts.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json response for visitor types.
   */
  public function getVisitorTypes(Request $request) {
    $visit_date = $request->request->get('visit_date');
    $shifts = $request->request->get('shifts');
    $visitor_types = $this->ticketBooking->getVisitorTypesData($shifts, $visit_date);

    $response = new CacheableJsonResponse($visitor_types, 200);

    // Adding cacheability metadata, so whenever, cache invalidates, this
    // url's cached response also gets invalidate.
    $cacheMeta = new CacheableMetadata();

    // Adding cache tags.
    $cacheMeta->addCacheTags(['booking_steps:getVisitorTypes']);
    $response->addCacheableDependency($cacheMeta);

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

    $response = new CacheableJsonResponse($sexes, 200);

    // Adding cacheability metadata, so whenever, cache invalidates, this
    // url's cached response also gets invalidate.
    $cacheMeta = new CacheableMetadata();

    // Adding cache tags.
    $cacheMeta->addCacheTags(['booking_steps:getSexes']);
    $response->addCacheableDependency($cacheMeta);

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
    $shifts = $request->request->get('shifts');
    $final_visitor_list = $request->request->get('final_visitor_list');
    $valid = $this->ticketBooking->validateVisitorsList($final_visitor_list['data']);
    if ($valid === 1) {
      $sales_number = $this->ticketBooking->generateSalesNumber();
      $flag = FALSE;
      foreach ($final_visitor_list['data'] as $key => $value) {
        $price = $this->ticketBooking->getVisitorPrice($shifts, $final_visitor_list['visit_date'], $value['ID']);
        if ($price !== NULL) {
          foreach ($value['Book'] as $k => $val) {
            $flag = FALSE;
            // Generate ticket number.
            $ticket_number = $this->ticketBooking->generateTicketNumber();
            $final_visitor_list['data'][$key]['Book'][$k]['ticket_id'] = $ticket_number;
            $final_visitor_list['data'][$key]['Price'] = $price;
            if ($this->ticketBooking->saveTicket($final_visitor_list['data'][$key], $val, $ticket_number, $shifts, $sales_number, $final_visitor_list['visit_date'])) {
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
        else {
          $responseData->err = TRUE;
          $responseData->message = $this->t('Unable to validate price for requested visitor.');
          $response->setData($responseData);
          return $response;
        }
      }
      $order_total = $this->ticketBooking->getOrderTotal($sales_number);
      if ($flag && ($order_total > 0)) {
        $final_visitor_list['sales_number'] = $sales_number;
        $final_visitor_list['status'] = TRUE;
        $response->setData($final_visitor_list);
        return $response;
      }
      $responseData->err = TRUE;
      $responseData->message = $this->t('Total amount is not valid as per requested order.');
      $response->setData($responseData);
      return $response;
    }
    else {
      $responseData->err = TRUE;
      if ($valid === 2) {
        $responseData->message = $this->t('Children under the age of 8, must be accompanied by an Adult.');
      }
      else {
        $responseData->message = $this->t('Please fill the complete form.');
      }
      $response->setData($responseData);
      return $response;
    }
  }

  /**
   * Display the payment result and status.
   *
   * @param string $ref_number
   *   The reference number.
   */
  public function paymentStatus($ref_number) {
    $booking_info = $this->bookingPayment->getTicketDetails($ref_number);
    $booking_info['kidz_url'] = $this->configFactory->get('alshaya_kz_transac_lite.settings')->get('tnc_url');
    $theme = isset($booking_info['payment_status']) && $booking_info['payment_status'] == 'complete' ? 'payment_success' : 'payment_failed';
    return [
      '#theme' => $theme,
      '#booking_info' => $booking_info,
      '#attached' => ['drupalSettings' => ['clear_storage' => 1]],
    ];
  }

}
