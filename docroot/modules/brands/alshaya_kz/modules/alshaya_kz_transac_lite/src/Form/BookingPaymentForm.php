<?php

namespace Drupal\alshaya_kz_transac_lite\Form;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\alshaya_kz_transac_lite\BookingPaymentManager;
use Drupal\alshaya_kz_transac_lite\TicketBookingManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\alshaya_knet\Helper\KnetHelper;

/**
 * BookingPaymentForm provide a form to do the booking payment.
 */
class BookingPaymentForm extends FormBase {

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
   * K-Net Helper class.
   *
   * @var \Drupal\alshaya_knet\Helper\KnetHelper
   */
  protected $knetHelper;

  /**
   * BookingPaymentForm constructor.
   *
   * @param \Drupal\alshaya_kz_transac_lite\TicketBookingManager $ticket_booking
   *   The ticket booking object.
   * @param \Drupal\alshaya_kz_transac_lite\BookingPaymentManager $booking_payment
   *   The booking payment object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory object.
   * @param \Drupal\alshaya_knet\Helper\KnetHelper $knet_helper
   *   Kidzania ticket booking K-net helper.
   */
  public function __construct(TicketBookingManager $ticket_booking,
                              BookingPaymentManager $booking_payment,
                              ConfigFactoryInterface $config_factory,
                              KnetHelper $knet_helper) {

    $this->ticketBooking = $ticket_booking;
    $this->bookingPayment = $booking_payment;
    $this->configFactory = $config_factory;
    $this->knetHelper = $knet_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_kz_transac_lite.booking_manager'),
      $container->get('alshaya_kz_transac_lite.booking_payment_manager'),
      $container->get('config.factory'),
      $container->get('alshaya_knet.helper')
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
      '#title' => $this->t('Name'),
      '#title_display' => 'invisible',
      '#type' => 'textfield',
      '#placeholder' => $this->t('Name'),
      '#maxlength' => 50,
      '#required' => TRUE,
      '#attributes' => ['autocomplete' => 'off'],
    ];

    $form['email'] = [
      '#title' => $this->t('Email'),
      '#title_display' => 'invisible',
      '#type' => 'email',
      '#placeholder' => $this->t('Email'),
      '#required' => TRUE,
      '#attributes' => ['autocomplete' => 'off'],
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
      '#prefix' => '<div class="payment_option">',
      '#suffix' => '<span class="k-net"></span></div>',
    ];

    $form['payment_option']['cybersource'] = [
      '#type' => 'markup',
      '#markup' => '<div class="payment_option">
          <p class="future_option">' . $this->t('Please note we are in the process of adding Credit Card payment option') . '</p>
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
    $mobile = $form_state->getValue('mobile');
    if (empty($mobile['mobile'])) {
      $form_state->setErrorByName('mobile', $this->t('Please enter your Mobile number.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $visitor_types = '';
    $final_visitor_list = json_decode($form_state->getValue('booking_info'), NULL);
    if (isset($final_visitor_list)) {
      foreach ($final_visitor_list->data as $value) {
        $visitor_types .= $value->Description . '-' . $value->Ticket->count . ',';
      }
      $order_total = $this->ticketBooking->getOrderTotal($final_visitor_list->sales_number);
      $booking_info = $this->bookingPayment->getTicketDetails($final_visitor_list->sales_number);
      if (!empty($order_total) && empty($booking_info)) {
        $booking = [
          'name' => $form_state->getValue('name'),
          'email' => $form_state->getValue('email'),
          'mobile' => $form_state->getValue('mobile'),
          'payment_type' => 'knet',
          'sales_number' => $final_visitor_list->sales_number,
          'visitor_types' => rtrim($visitor_types, ','),
          'visit_date' => $final_visitor_list->visit_date,
          'order_total' => number_format((float) $order_total, 3, '.', ''),
          'ticket_info' => $final_visitor_list->data,
        ];
        // Save ticket details.
        if ($this->bookingPayment->saveTicketDetails($booking, $final_visitor_list->sales_number)) {
          // Initiate K-Net request.
          $this->knetHelper->setCartId($final_visitor_list->sales_number);
          $this->knetHelper->setOrderId($final_visitor_list->sales_number);
          $request = $this->knetHelper->initKnetRequest($order_total);

          $response = new RedirectResponse($request['url']);
          $response->send();
          exit;
        }
      }
    }
    // Redirect to home page if booking steps are not followed properly.
    $form_state->setRedirect('<front>');
  }

}
