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
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager,
                              TicketBookingManager $ticketBooking,
                              BookingPaymentManager $bookingPayment,
                              ConfigFactoryInterface $config_factory) {

    $this->entityTypeManager = $entityTypeManager;
    $this->ticketBooking = $ticketBooking;
    $this->bookingPayment = $bookingPayment;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('alshaya_kz_transac_lite.booking_manager'),
      $container->get('alshaya_kz_transac_lite.booking_payment_manager'),
      $container->get('config.factory')
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
    $get_parks = json_decode($this->ticketBooking->tempStore()->get('get_parks'));
    $visit_date = $this->ticketBooking->tempStore()->get('visit_date');
    $final_visitor_list = json_decode($this->ticketBooking->tempStore()->get('final_visitor_list'));

    $form['visit_date'] = [
      '#markup' => $visit_date,
    ];

    $form['park'] = [
      '#markup' => $get_parks->getParksResult->Park->Name,
    ];

    $form['order_total'] = [
      '#markup' => $final_visitor_list->total->price,
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
      '#title' => $this->t('Mobile Number'),
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
      '#suffix' => '<div class="payment_option">
          <span class="k-net">K-NET</span>
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

    $sales_number = $this->ticketBooking->tempStore()->get('sales_number');
    $visit_date = $this->ticketBooking->tempStore()->get('visit_date');
    $final_visitor_list = json_decode($this->ticketBooking->tempStore()->get('final_visitor_list'));
    $visitor_types = '';
    foreach ($final_visitor_list->data as $value) {
      $visitor_types .= $value->Description . '-' . $value->Ticket->count . ',';
    }

    $order_total = $this->ticketBooking->tempStore()->get('order_total');

    $knet = ($form_state->getValue('knet')) ? 'knet' : '';
    $booking = [
      'name' => $form_state->getValue('name'),
      'email' => $form_state->getValue('email'),
      'mobile' => $form_state->getValue('mobile'),
      'payment_type' => $knet,
      'sales_number' => $sales_number,
      'visitor_types' => rtrim($visitor_types, ','),
      'visit_date' => $visit_date,
      'order_total' => $order_total,
      'order_date' => date('Y-m-d'),
    ];

    // Create content for ticket entity type.
    if ($this->bookingPayment->saveTicketDetails($booking, $sales_number)) {
      $this->ticketBooking->tempStore()->set('booking_info', json_encode($booking));
      $form_state->setRedirect('alshaya_kz_transac_lite.payemnt_option', ['option' => $knet]);
    }
  }

}
