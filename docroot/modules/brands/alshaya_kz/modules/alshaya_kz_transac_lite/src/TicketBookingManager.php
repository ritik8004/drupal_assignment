<?php

namespace Drupal\alshaya_kz_transac_lite;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * TicketBookingManager integrate and receive response from kidsoft API.
 */
class TicketBookingManager {

  /**
   * Cache Backend object for "cache.data".
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The soap client object.
   *
   * @var TicketBookingManager
   */
  protected $soapClient;

  /**
   * TicketBooking constructor.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend object for "cache.data".
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory object.
   */
  public function __construct(CacheBackendInterface $cache,
                              LoggerChannelFactoryInterface $logger_factory,
                              ConfigFactoryInterface $config_factory) {

    $this->cache = $cache;
    $this->logger = $logger_factory->get('alshaya_kz_transac_lite');
    $this->configFactory = $config_factory;
    $this->soapClient = new \SoapClient($this->configFactory->get('alshaya_kz_transac_lite.settings')->get('service_url'));
  }

  /**
   * Get cache id for booking steps.
   *
   * @return string
   *   Cache key.
   */
  public function getTicketBookingCachedId($key) {
    return 'alshaya_kz_transac_lite:' . $key;
  }

  /**
   * Get data from Cache for booking steps.
   *
   * @param string $key
   *   Key of the data to get from cache.
   *
   * @return array|null
   *   Data if found or null.
   */
  public function getTicketBookingCachedData($key) {
    $cid = $this->getTicketBookingCachedId($key);
    $static = &drupal_static($cid);
    if (!isset($static) && $cache = $this->cache->get($cid)) {
      $static = $cache->data;
    }
    return json_decode($static);
  }

  /**
   * Set data in Cache for booking steps.
   *
   * @param object $data
   *   Data to set in cache.
   * @param string $key
   *   Key of the data to get from cache.
   */
  public function setTicketBookingCachedData($data, $key) {
    $cid = $this->getTicketBookingCachedId($key);
    $data = json_encode($data);
    $this->cache->set($cid, $data, Cache::PERMANENT, ['alshaya_kz_transac_lite:kidsoft']);

    // Update data in static cache too.
    $static = &drupal_static($cid);
    $static = $data;
  }

  /**
   * To get the token values from Kidsoft.
   *
   * @return Token
   *   Return token with AuthString and AuthVal.
   */
  public function getToken() {
    if (isset($this->getTicketBookingCachedData('getToken')->authenticateResult)) {
      return $this->getTicketBookingCachedData('getToken');
    }
    // Get token from kidsoft.
    $settings = $this->configFactory->get('alshaya_kz_transac_lite.settings');
    try {
      $token = $this->soapClient->__soapCall("authenticate",
        [
          "parameters" =>
            [
              'user' => $settings->get('kidsoft_external_login'),
              'passwd' => $settings->get('kidsoft_external_pass'),
            ],
        ]
      );
      $this->setTicketBookingCachedData($token, 'getToken');
      return $token;
    }
    catch (\SoapFault $fault) {
      $this->logger->warning('API Error in getting token - %faultcode: %message', [
        '%faultcode' => $fault->faultcode,
        '%message' => $fault->faultstring,
      ]);
    }
  }

  /**
   * Get the parks data from kidsoft.
   *
   * @return object
   *   Object of parks data.
   */
  public function getParkData() {
    if (isset($this->getTicketBookingCachedData('getParks')->getParksResult)) {
      return $this->getTicketBookingCachedData('getParks');
    }

    try {
      $parks = $this->soapClient->__soapCall("getParks",
        [
          "parameters" =>
            [
              'auth' => [
                'AuthString' => $this->getToken()->authenticateResult->AuthString,
                'AuthVal' => $this->getToken()->authenticateResult->AuthVal,
              ],
            ],
        ]
      );
      // Re-initialize token if expired from kidsoft
      // by invalidating cache and get new one.
      if (empty($parks->getParksResult) && $this->getTicketBookingCachedData('getToken')) {
        Cache::invalidateTags(['alshaya_kz_transac_lite:kidsoft']);
      }
      $this->setTicketBookingCachedData($parks, 'getParkData');
      return $parks;
    }
    catch (\ SoapFault $fault) {
      $this->logger->warning('API Error in getting parks - %faultcode: %message', [
        '%faultcode' => $fault->faultcode,
        '%message' => $fault->faultstring,
      ]);
    }
  }

  /**
   * Get the shifts data from Kidsoft.
   *
   * @param string $visit_date
   *   The visit date.
   *
   * @return object
   *   object of shift data.
   */
  public function getShiftsData($visit_date) {
    try {
      $shifts = $this->soapClient->__soapCall("getShifts",
        [
          "parameters" =>
            [
              'park' => [
                'Name' => $this->getParkData()->getParksResult->Park->Name,
                'Prefix' => $this->getParkData()->getParksResult->Park->Prefix,
              ],
              'visitdate' => $visit_date,
              'auth' => [
                'AuthString' => $this->getToken()->authenticateResult->AuthString,
                'AuthVal' => $this->getToken()->authenticateResult->AuthVal,
              ],
            ],
        ]
      );
      return $shifts;
    }
    catch (\ SoapFault $fault) {
      $this->logger->warning('API Error in getting shifts - %faultcode: %message', [
        '%faultcode' => $fault->faultcode,
        '%message' => $fault->faultstring,
      ]);
    }
  }

  /**
   * Get list of visitor types from Kidsoft.
   *
   * @param string $shifts
   *   The visit date.
   * @param string $visit_date
   *   The visit date.
   *
   * @return object
   *   object of visitorTypes.
   */
  public function getVisitorTypesData(string $shifts, string $visit_date) {
    if (isset($this->getTicketBookingCachedData('getVisitorTypesData')->getVisitorTypesResult)) {
      return $this->getTicketBookingCachedData('getVisitorTypesData');
    }
    $shifts = json_decode($shifts);
    try {
      $visitorTypes = $this->soapClient->__soapCall("getVisitorTypes",
        [
          "parameters" =>
            [
              'park' => [
                'Name' => $this->getParkData()->getParksResult->Park->Name,
                'Prefix' => $this->getParkData()->getParksResult->Park->Prefix,
              ],
              'shift' => [
                'EndHour' => $shifts->getShiftsResult->Shift->EndHour,
                'ID' => $shifts->getShiftsResult->Shift->ID,
                'Name' => $shifts->getShiftsResult->Shift->Name,
                'StartHour' => $shifts->getShiftsResult->Shift->StartHour,
                'Tickets' => $shifts->getShiftsResult->Shift->Tickets,
              ],
              'visitdate' => $visit_date,
              'auth' => [
                'AuthString' => $this->getToken()->authenticateResult->AuthString,
                'AuthVal' => $this->getToken()->authenticateResult->AuthVal,
              ],
            ],
        ]
      );
      $this->setTicketBookingCachedData($visitorTypes, 'getVisitorTypesData');
      return $visitorTypes;
    }
    catch (\SoapFault $fault) {
      $this->logger->warning('API Error in getting visitor types - %faultcode: %message', [
        '%faultcode' => $fault->faultcode,
        '%message' => $fault->faultstring,
      ]);
    }
  }

  /**
   * Provide sexes object with detais from Kidsoft.
   *
   * @return object
   *   object of sexes data.
   */
  public function getSexesData() {
    if (isset($this->getTicketBookingCachedData('getSexesData')->getSexesResult)) {
      return $this->getTicketBookingCachedData('getSexesData');
    }
    try {
      $getSexes = $this->soapClient->__soapCall("getSexes",
        [
          "parameters" =>
            [
              'park' => [
                'Name' => $this->getParkData()->getParksResult->Park->Name,
                'Prefix' => $this->getParkData()->getParksResult->Park->Prefix,
              ],
              'auth' => [
                'AuthString' => $this->getToken()->authenticateResult->AuthString,
                'AuthVal' => $this->getToken()->authenticateResult->AuthVal,
              ],
            ],
        ]
      );
      $this->setTicketBookingCachedData($getSexes, 'getSexesData');
      return $getSexes;
    }
    catch (\ SoapFault $fault) {
      $this->logger->warning('API Error in getting sexes - %faultcode: %message', [
        '%faultcode' => $fault->faultcode,
        '%message' => $fault->faultstring,
      ]);
    }
  }

  /**
   * Generate Sales number to be used as reference for each generated ticket.
   *
   * @return string
   *   The sales number.
   */
  public function generateSalesNumber() {
    try {
      $generateSaleNumber = $this->soapClient->__soapCall("generateSaleNumber",
        [
          "parameters" =>
            [
              'park' => [
                'Name' => $this->getParkData()->getParksResult->Park->Name,
                'Prefix' => $this->getParkData()->getParksResult->Park->Prefix,
              ],
              'auth' => [
                'AuthString' => $this->getToken()->authenticateResult->AuthString,
                'AuthVal' => $this->getToken()->authenticateResult->AuthVal,
              ],
            ],
        ]
      );
      return $generateSaleNumber->generateSaleNumberResult;
    }
    catch (\SoapFault $fault) {
      $this->logger->warning('API Error in generating sales number - %faultcode: %message', [
        '%faultcode' => $fault->faultcode,
        '%message' => $fault->faultstring,
      ]);
    }
  }

  /**
   * Generate the tickect number for each selected visitors from Kidsoft.
   *
   * @return string
   *   The Ticket number.
   */
  public function generateTicketNumber() {
    try {
      $generateTicketNumber = $this->soapClient->__soapCall("generateTicketNumber",
        [
          "parameters" =>
            [
              'park' => [
                'Name' => $this->getParkData()->getParksResult->Park->Name,
                'Prefix' => $this->getParkData()->getParksResult->Park->Prefix,
              ],
              'auth' => [
                'AuthString' => $this->getToken()->authenticateResult->AuthString,
                'AuthVal' => $this->getToken()->authenticateResult->AuthVal,
              ],
            ],
        ]
      );
      return $generateTicketNumber->generateTicketNumberResult;
    }
    catch (\SoapFault $fault) {
      $this->logger->warning('API Error in generating ticket number - %faultcode: %message', [
        '%faultcode' => $fault->faultcode,
        '%message' => $fault->faultstring,
      ]);
    }
  }

  /**
   * Save the visitor details in kidsoft platform in the form of ticket.
   *
   * @param array $visitor_list
   *   Array of visitors.
   * @param array $book_ticket
   *   Array of booked tickets.
   * @param string $ticket_number
   *   The ticket number.
   * @param string $shifts
   *   The shifts data.
   * @param string $sales_number
   *   The sales number.
   * @param string $visit_date
   *   The visit date.
   *
   * @return bool
   *   return a boolean value.
   */
  public function saveTicket(array $visitor_list, array $book_ticket, $ticket_number, $shifts, $sales_number, $visit_date) {
    $shifts = json_decode($shifts);
    try {
      $saveTicket = $this->soapClient->__soapCall("saveTicket",
        [
          "parameters" =>
            [
              'ticket' => [
                'Age' => $book_ticket['age'],
                'Barcode' => $ticket_number,
                'BarrasDescuento' => '',
                'CustomerIP' => 0,
                'Description' => '',
                'IdTax' => 0,
                'MemberID' => 0,
                'Name' => $book_ticket['name'],
                'Percent' => 0,
                'SaleNum' => $sales_number,
                'SalePrice' => $visitor_list['Price'],
                'ServerIP' => 'LOCALHOST',
                'Sex' => [
                  'Description' => $book_ticket['gender']['description'],
                  'Initial' => $book_ticket['gender']['initial'],
                ],
                'Shift' => [
                  'EndHour' => $shifts->getShiftsResult->Shift->EndHour,
                  'ID' => $shifts->getShiftsResult->Shift->ID,
                  'Name' => $shifts->getShiftsResult->Shift->Name,
                  'StartHour' => $shifts->getShiftsResult->Shift->StartHour,
                  'Tickets' => $shifts->getShiftsResult->Shift->Tickets,
                ],
                'Shortcode' => 'ONLINE',
                'Status' => [
                  'Description' => 'PENDING',
                  'ID' => 0,
                ],
                'UnitTax' => 0,
                'VisitDate' => $visit_date,
                'VisitorType' => [
                  'AliasID' => $visitor_list['AliasID'],
                  'Description' => $visitor_list['Description'],
                  'EndTime' => $visitor_list['EndTime'],
                  'ID' => $visitor_list['ID'],
                  'MaxAge' => $visitor_list['MaxAge'],
                  'MinAge' => $visitor_list['MinAge'],
                  'Price' => $visitor_list['Price'],
                  'StartTime' => $visitor_list['StartTime'],
                ],
              ],
              'park' => [
                'Name' => $this->getParkData()->getParksResult->Park->Name,
                'Prefix' => $this->getParkData()->getParksResult->Park->Prefix,
              ],
              'auth' => [
                'AuthString' => $this->getToken()->authenticateResult->AuthString,
                'AuthVal' => $this->getToken()->authenticateResult->AuthVal,
              ],
            ],
        ]
      );
      return $saveTicket->saveTicketResult;
    }
    catch (\SoapFault $fault) {
      $this->logger->warning('API Error in saving ticket - %faultcode: %message', [
        '%faultcode' => $fault->faultcode,
        '%message' => $fault->faultstring,
      ]);
    }
  }

  /**
   * Generate order total.
   *
   * @return int
   *   integer Total amount.
   */
  public function getOrderTotal($sales_number) {
    try {
      $getOrderTotal = $this->soapClient->__soapCall("getOrderTotal",
        [
          "parameters" =>
            [
              'saleNum' => $sales_number,
              'park' => [
                'Name' => $this->getParkData()->getParksResult->Park->Name,
                'Prefix' => $this->getParkData()->getParksResult->Park->Prefix,
              ],
              'auth' => [
                'AuthString' => $this->getToken()->authenticateResult->AuthString,
                'AuthVal' => $this->getToken()->authenticateResult->AuthVal,
              ],
            ],
        ]
      );
    }
    catch (\SoapFault $fault) {
      $this->logger->warning('API Error in getting total order - %faultcode: %message', [
        '%faultcode' => $fault->faultcode,
        '%message' => $fault->faultstring,
      ]);
    }

    return $getOrderTotal->getOrderTotalResult;
  }

  /**
   * Activate order by requesting to Kidsoft after successful payment.
   *
   * @param string $sales_number
   *   The sales number.
   *
   * @return bool
   *   Activate the order status.
   */
  public function activateOrder(string $sales_number) {
    try {
      $activateOrder = $this->soapClient->__soapCall("activateOrder",
        [
          "parameters" =>
            [
              'saleNum' => $sales_number,
              'park' => [
                'Name' => $this->getParkData()->getParksResult->Park->Name,
                'Prefix' => $this->getParkData()->getParksResult->Park->Prefix,
              ],
              'auth' => [
                'AuthString' => $this->getToken()->authenticateResult->AuthString,
                'AuthVal' => $this->getToken()->authenticateResult->AuthVal,
              ],
            ],
        ]
      );
      return $activateOrder->activateOrderResult;
    }
    catch (\SoapFault $fault) {
      $this->logger->warning('API Error in activating order - %faultcode: %message', [
        '%faultcode' => $fault->faultcode,
        '%message' => $fault->faultstring,
      ]);
    }
  }

  /**
   * Validate infants or kids with adult.
   *
   * @param array $final_visitor_list
   *   Array of visitors list.
   *
   * @return bool
   *   A boolean flag.
   */
  public function validateVisitorsList(array $final_visitor_list) {
    $flag = FALSE;
    $is_child = FALSE;
    foreach ($final_visitor_list as $value) {
      $flag = FALSE;
      // Is infants available.
      if ($value['Book'] && (($value['ID'] == 0 || $value['ID'] == 1))) {
        $is_child = TRUE;
      }
      // Is kid available not need adult.
      if ($value['Book'] && ($value['ID'] == 2)) {
        $flag = TRUE;
      }
      // Adult is required with infants.
      if ($value['Book'] && $value['ID'] == 4 && $is_child) {
        $flag = TRUE;
      }
      // Adult must be allowed with infants or kids only.
      if ($value['Book'] && $value['ID'] == 4 && !$is_child) {
        if (array_search(2, array_column($value, 'ID'))) {
          $flag = TRUE;
        }
      }
    }
    return $flag;
  }

}
