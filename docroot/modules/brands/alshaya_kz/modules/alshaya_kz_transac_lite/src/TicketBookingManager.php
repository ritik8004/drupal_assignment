<?php

namespace Drupal\alshaya_kz_transac_lite;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\user\PrivateTempStoreFactory;

/**
 * TicketBookingManager integrate and recieves response from kidsoft API.
 */
class TicketBookingManager {

  /**
   * The private temp store.
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $privateTempStore;

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
   * @param \Drupal\user\PrivateTempStoreFactory $temp_store_factory
   *   Temporary store factory object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory object.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory,
                              LoggerChannelFactoryInterface $logger_factory,
                              ConfigFactoryInterface $config_factory) {

    $this->privateTempStore = $temp_store_factory;
    $this->store = $this->privateTempStore->get('alshaya_kz_transac_lite_cart');
    $this->logger = $logger_factory->get('alshaya_kz_transac_lite');
    $this->configFactory = $config_factory;
    $this->soapClient = new \SoapClient($this->configFactory->get('alshaya_kz_transac_lite.settings')->get('service_url'));
  }

  /**
   * The store object.
   *
   * @return \Drupal\user\PrivateTempStoreFactory
   *   Return private temporary store factory object.
   */
  public function tempStore() {
    return $this->store;
  }

  /**
   * Removes all the keys from the store collection.
   */
  public function deleteStore() {
    $keys = [
      'get_parks',
      'get_shifts',
      'visit_date',
      'visitor_types',
      'get_sexes',
      'sales_number',
      'order_total',
      'final_visitor_list',
    ];
    foreach ($keys as $key) {
      $this->store->delete($key);
    }
  }

  /**
   * To get the token data from Kidsoft.
   *
   * @return Token
   *   Return token with AuthString and AuthVal.
   */
  public function getToken() {
    $settings = $this->configFactory->get('alshaya_kz_transac_lite.settings');
    try {
      return $this->soapClient->__soapCall("authenticate",
        [
          "parameters" =>
          [
            'user' => $settings->get('user'),
            'passwd' => $settings->get('passwd'),
          ],
        ]
      );
    }
    catch (\SoapFault $fault) {
      $this->logger->warning('API Error in getting token - %faultcode: %message', [
        '%faultcode' => $fault->faultcode,
        '%message' => $fault->faultstring,
      ]);
    }
  }

  /**
   * Get the parks data from kidsoft API.
   *
   * @return object
   *   Object of parks data.
   */
  public function getParkData() {
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

      $this->tempStore()->set('get_parks', json_encode($parks));

    }
    catch (\ SoapFault $fault) {
      $this->logger->warning('API Error in getting parks - %faultcode: %message', [
        '%faultcode' => $fault->faultcode,
        '%message' => $fault->faultstring,
      ]);
    }

    return $parks;
  }

  /**
   * Get the sifts data from Kidsoft.
   *
   * @param string $visit_date
   *   The visit date.
   *
   * @return object
   *   object of shift data.
   */
  public function getShiftsData($visit_date) {
    $get_parks = json_decode($this->tempStore()->get('get_parks'));

    try {
      $shifts = $this->soapClient->__soapCall("getShifts",
        [
          "parameters" =>
          [
            'park' => [
              'Name' => $get_parks->getParksResult->Park->Name,
              'Prefix' => $get_parks->getParksResult->Park->Prefix,
            ],
            'visitdate' => $visit_date,
            'auth' => [
              'AuthString' => $this->getToken()->authenticateResult->AuthString,
              'AuthVal' => $this->getToken()->authenticateResult->AuthVal,
            ],
          ],
        ]
      );
      $this->tempStore()->set('visit_date', $visit_date);
      $this->tempStore()->set('get_shifts', json_encode($shifts));

    }
    catch (\ SoapFault $fault) {
      $this->logger->warning('API Error in getting shifts - %faultcode: %message', [
        '%faultcode' => $fault->faultcode,
        '%message' => $fault->faultstring,
      ]);
    }

    return $shifts;
  }

  /**
   * Get list of visitor types from Kidsoft.
   *
   * @return object
   *   object of visitorTypes.
   */
  public function getVisitorTypesData() {
    $get_parks = json_decode($this->tempStore()->get('get_parks'));
    $get_shifts = json_decode($this->tempStore()->get('get_shifts'));
    $visit_date = $this->tempStore()->get('visit_date');

    try {
      $visitorTypes = $this->soapClient->__soapCall("getVisitorTypes",
        [
          "parameters" =>
          [
            'park' => [
              'Name' => $get_parks->getParksResult->Park->Name,
              'Prefix' => $get_parks->getParksResult->Park->Prefix,
            ],
            'shift' => [
              'EndHour' => $get_shifts->getShiftsResult->Shift->EndHour,
              'ID' => $get_shifts->getShiftsResult->Shift->ID,
              'Name' => $get_shifts->getShiftsResult->Shift->Name,
              'StartHour' => $get_shifts->getShiftsResult->Shift->StartHour,
              'Tickets' => $get_shifts->getShiftsResult->Shift->Tickets,
            ],
            'visitdate' => $visit_date,
            'auth' => [
              'AuthString' => $this->getToken()->authenticateResult->AuthString,
              'AuthVal' => $this->getToken()->authenticateResult->AuthVal,
            ],
          ],
        ]
      );
      $this->tempStore()->set('visitor_types', json_encode($visitorTypes));

    }
    catch (\SoapFault $fault) {
      $this->logger->warning('API Error in getting visitor types - %faultcode: %message', [
        '%faultcode' => $fault->faultcode,
        '%message' => $fault->faultstring,
      ]);
    }

    return $visitorTypes;
  }

  /**
   * Provide sexes object with detais from Kidsoft.
   *
   * @return object
   *   object of sexes data.
   */
  public function getSexesData() {
    $get_parks = json_decode($this->tempStore()->get('get_parks'));

    try {
      $getSexes = $this->soapClient->__soapCall("getSexes",
        [
          "parameters" =>
          [
            'park' => [
              'Name' => $get_parks->getParksResult->Park->Name,
              'Prefix' => $get_parks->getParksResult->Park->Prefix,
            ],
            'auth' => [
              'AuthString' => $this->getToken()->authenticateResult->AuthString,
              'AuthVal' => $this->getToken()->authenticateResult->AuthVal,
            ],
          ],
        ]
      );
      $this->tempStore()->set('get_sexes', json_encode($getSexes));

    }
    catch (\ SoapFault $fault) {
      $this->logger->warning('API Error in getting sexes - %faultcode: %message', [
        '%faultcode' => $fault->faultcode,
        '%message' => $fault->faultstring,
      ]);
    }

    return $getSexes;
  }

  /**
   * Generate Sales number to be used as reference for each generated ticket.
   *
   * @return string
   *   The sales number.
   */
  public function generateSalesNumber() {
    $get_parks = json_decode($this->tempStore()->get('get_parks'));
    try {
      $generateSaleNumber = $this->soapClient->__soapCall("generateSaleNumber",
        [
          "parameters" =>
          [
            'park' => [
              'Name' => $get_parks->getParksResult->Park->Name,
              'Prefix' => $get_parks->getParksResult->Park->Prefix,
            ],
            'auth' => [
              'AuthString' => $this->getToken()->authenticateResult->AuthString,
              'AuthVal' => $this->getToken()->authenticateResult->AuthVal,
            ],
          ],
        ]
      );
      $this->tempStore()->set('sales_number', $generateSaleNumber->generateSaleNumberResult);

    }
    catch (\SoapFault $fault) {
      $this->logger->warning('API Error in generating sales number - %faultcode: %message', [
        '%faultcode' => $fault->faultcode,
        '%message' => $fault->faultstring,
      ]);
    }

    return $generateSaleNumber->generateSaleNumberResult;
  }

  /**
   * Generate the tickect number for each selected visitors from Kidsoft.
   *
   * @return string
   *   The Ticket number.
   */
  public function generateTicketNumber() {
    $get_parks = json_decode($this->tempStore()->get('get_parks'));
    try {
      $generateTicketNumber = $this->soapClient->__soapCall("generateTicketNumber",
        [
          "parameters" =>
          [
            'park' => [
              'Name' => $get_parks->getParksResult->Park->Name,
              'Prefix' => $get_parks->getParksResult->Park->Prefix,
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
      $this->logger->warning('API Error in generating ticket number - %faultcode: %message', [
        '%faultcode' => $fault->faultcode,
        '%message' => $fault->faultstring,
      ]);
    }

    return $generateTicketNumber->generateTicketNumberResult;
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
   * @param string $sales_number
   *   The sales number.
   *
   * @return bool
   *   return a boolean value.
   */
  public function saveTicket(array $visitor_list, array $book_ticket, $ticket_number, $sales_number) {
    $get_parks = json_decode($this->tempStore()->get('get_parks'));
    $get_shifts = json_decode($this->tempStore()->get('get_shifts'));
    $visit_date = $this->tempStore()->get('visit_date');

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
                'EndHour' => $get_shifts->getShiftsResult->Shift->EndHour,
                'ID' => $get_shifts->getShiftsResult->Shift->ID,
                'Name' => $get_shifts->getShiftsResult->Shift->Name,
                'StartHour' => $get_shifts->getShiftsResult->Shift->StartHour,
                'Tickets' => $get_shifts->getShiftsResult->Shift->Tickets,
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
              'Name' => $get_parks->getParksResult->Park->Name,
              'Prefix' => $get_parks->getParksResult->Park->Prefix,
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
      $this->logger->warning('API Error in saving ticket - %faultcode: %message', [
        '%faultcode' => $fault->faultcode,
        '%message' => $fault->faultstring,
      ]);
    }

    return $saveTicket->saveTicketResult;
  }

  /**
   * Generate order total.
   *
   * @return int
   *   integer Total amount.
   */
  public function getOrderTotal($sales_number) {
    $get_parks = json_decode($this->tempStore()->get('get_parks'));
    try {
      $getOrderTotal = $this->soapClient->__soapCall("getOrderTotal",
        [
          "parameters" =>
          [
            'saleNum' => $sales_number,
            'park' => [
              'Name' => $get_parks->getParksResult->Park->Name,
              'Prefix' => $get_parks->getParksResult->Park->Prefix,
            ],
            'auth' => [
              'AuthString' => $this->getToken()->authenticateResult->AuthString,
              'AuthVal' => $this->getToken()->authenticateResult->AuthVal,
            ],
          ],
        ]
      );
      $this->tempStore()->set('order_total', $getOrderTotal->getOrderTotalResult);

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
   * @return bool
   *   Activate the order status.
   */
  public function activateOrder($sales_number) {
    $get_parks = json_decode($this->tempStore()->get('get_parks'));
    try {
      $activateOrder = $this->soapClient->__soapCall("activateOrder",
        [
          "parameters" =>
          [
            'saleNum' => $sales_number,
            'park' => [
              'Name' => $get_parks->getParksResult->Park->Name,
              'Prefix' => $get_parks->getParksResult->Park->Prefix,
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
      $this->logger->warning('API Error in activating order - %faultcode: %message', [
        '%faultcode' => $fault->faultcode,
        '%message' => $fault->faultstring,
      ]);
    }

    return $activateOrder->activateOrderResult;
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
