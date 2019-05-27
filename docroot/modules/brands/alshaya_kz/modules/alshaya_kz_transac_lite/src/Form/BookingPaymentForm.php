<?php

namespace Drupal\alshaya_kz_transac_lite\Form;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\alshaya_kz_transac_lite\BookingPaymentManager;
use Drupal\alshaya_kz_transac_lite\TicketBookingManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\alshaya_kz_transac_lite\Helper\TicketBookingKnetHelper;

/**
 * BookingPaymentForm provide a form to do the booking payment.
 */
class BookingPaymentForm extends FormBase {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The ticket booking.
   *
   * @var \Drupal\alshaya_kz_transac_lite\TicketBookingManager
   */
  protected $ticketBooking;

  /**
   * The booking payment.
   *
   * @var \Drupal\alshaya_kz_transac_lite\TicketBookingManager
   */
  protected $bookingPayment;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Kidzania K-Net Helper class.
   *
   * @var \Drupal\alshaya_kz_transac_lite\Helper\TicketBookingKnetHelper
   */
  protected $ticketBookingKnetHelper;

  /**
   * BookingPaymentForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity manager.
   * @param \Drupal\alshaya_kz_transac_lite\TicketBookingManager $ticketBooking
   *   The ticket booking object.
   * @param \Drupal\alshaya_kz_transac_lite\BookingPaymentManager $bookingPayment
   *   The booking payment object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory object.
   * @param \Drupal\alshaya_kz_transac_lite\Helper\TicketBookingKnetHelper $ticket_booking_knet_helper
   *   Kidzania ticket booking K-net helper.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager,
                              TicketBookingManager $ticketBooking,
                              BookingPaymentManager $bookingPayment,
                              ConfigFactoryInterface $config_factory,
                              TicketBookingKnetHelper $ticket_booking_knet_helper) {

    $this->entityTypeManager = $entityTypeManager;
    $this->ticketBooking = $ticketBooking;
    $this->bookingPayment = $bookingPayment;
    $this->configFactory = $config_factory;
    $this->ticketBookingKnetHelper = $ticket_booking_knet_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('alshaya_kz_transac_lite.booking_manager'),
      $container->get('alshaya_kz_transac_lite.booking_payment_manager'),
      $container->get('config.factory'),
      $container->get('alshaya_kz_transac_lite.helper')
    );
  }

  /**
   * Get form id.
   *
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'booking_payment_form';
  }

  /**
   * Build a form.
   *
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['booking_info'] = [
      '#type' => 'hidden',
      '#attributes' => ['id' => ['booking-info']],
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#placeholder' => $this->t('Name'),
      '#maxlength' => 50,
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#placeholder' => $this->t('Email'),
      '#required' => TRUE,
    ];

    $form['mobile'] = [
      '#type' => 'mobile_number',
      '#title' => '',
      '#placeholder' => $this->t('Mobile Number'),
      '#required' => TRUE,
    ];

    $tnc_url = $this->configFactory->get('alshaya_kz_transac_lite.settings')->get('tnc_url');
    $tnc = Link::fromTextAndUrl($this->t('Terms & condition'), Url::fromUri($tnc_url, ['attributes' => ['target' => '_blank']]))->toString();
    $form['approve'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Accept our') . ' ' . $tnc,
      '#required' => TRUE,
    ];

    $form['payment_option']['knet'] = [
      '#type' => 'checkbox',
      '#required' => TRUE,
      '#suffix' => '<div class="knet-option">
          <span class="k-net"></span>
        </div>',
    ];

    $form['payment_option']['cybersource'] = [
      '#type' => 'markup',
      '#markup' => '<div class="payment_option">
          <p class="future_option">' . $this->t('please note: we are in the process of adding credit card payment option') . '</p>
          </div>',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Proceed to pay'),
      '#attributes' => ['class' => ['continueBtn', 'actionBut']],
    ];

    $form['#theme'] = 'booking_payment';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $name = $form_state->getValue('name');
    if (preg_match('/[#$\_\!@%^&*()+=\-\[\]\';,.\/{}|":<>?~\\\\]/', $name)) {
      $form_state->setErrorByName('name', $this->t('Please enter a valid name.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $visitor_types = '';
    $final_visitor_list = json_decode($form_state->getValue('booking_info'));
    if (isset($final_visitor_list)) {
      foreach ($final_visitor_list->data as $value) {
        $visitor_types .= $value->Description . '-' . $value->Ticket->count . ',';
      }

      $order_total = $this->ticketBooking->getOrderTotal($final_visitor_list->sales_number);
      $knet = ($form_state->getValue('knet')) ? 'knet' : '';
      $booking = [
        'name' => $form_state->getValue('name'),
        'email' => $form_state->getValue('email'),
        'mobile' => $form_state->getValue('mobile'),
        'payment_type' => $knet,
        'sales_number' => $final_visitor_list->sales_number,
        'visitor_types' => rtrim($visitor_types, ','),
        'visit_date' => $final_visitor_list->visit_date,
        'order_total' => $order_total,
        'order_date' => date('Y-m-d'),
      ];

      // Create content for ticket entity type.
      if ($this->bookingPayment->saveTicketDetails($booking, $final_visitor_list->sales_number)) {
        $this->ticketBookingKnetHelper->setOrderId($final_visitor_list->sales_number);
        $request = $this->ticketBookingKnetHelper->initKnetRequest($order_total);
        $response = new RedirectResponse($request['url']);
        $response->send();
      }
    }
  }

}
