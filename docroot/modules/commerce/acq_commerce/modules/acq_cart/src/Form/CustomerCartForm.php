<?php

namespace Drupal\acq_cart\Form;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_commerce\UpdateCartErrorEvent;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CustomerCartForm.
 *
 * @package Drupal\acq_cart\Form
 */
class CustomerCartForm extends FormBase {

  /**
   * Drupal\acq_cart\CartStorageInterface definition.
   *
   * @var \Drupal\acq_cart\Cart
   */
  protected $cart;

  /**
   * Drupal\acq_cart\CartStorageInterface definition.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * The success message to be displayed on coupon apply.
   *
   * @var string
   */
  protected $successMessage = NULL;

  /**
   * Constructor.
   *
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   The cart storage.
   */
  public function __construct(CartStorageInterface $cart_storage) {
    $this->cartStorage = $cart_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acq_cart.cart_storage')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'customer_cart_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // We always want this cache context.
    $form['#cache']['contexts'][] = 'session';
    $form['#cache']['contexts'][] = 'cookies:Drupal_visitor_acq_cart_id';

    $cart = $this->cartStorage->getCart(FALSE);
    if (empty($cart)) {
      return $form;
    }

    // Add this cache tag if cart exists.
    $form['#cache']['tags'][] = 'cart:' . $cart->id();

    $items = NULL;

    if ($cart) {
      $items = $cart->items();
    }

    $form['cart'] = [
      '#type' => 'table',
      '#header' => [
        t('Product'),
        t('Quantity'),
        t('Price'),
      ],
      '#empty' => t('There are no products in your cart yet.'),
    ];

    if (empty($items)) {
      return $form;
    }

    foreach ($items as $index => $line_item) {
      // Ensure object notation.
      $line_item = (object) $line_item;

      $id = $line_item->sku;

      $form['cart'][$id]['name'] = [
        '#markup' => $line_item->name,
      ];

      $form['cart'][$id]['quantity'] = [
        '#type' => 'number',
        '#default_value' => $line_item->qty,
      ];

      $form['cart'][$id]['price'] = [
        '#plain_text' => $line_item->price,
      ];
    }

    $form['totals'] = [
      '#type' => 'table',
    ];

    $totals = $cart->totals();

    $form['totals']['sub'] = [
      'label' => ['#plain_text' => t('Subtotal')],
      'value' => ['#plain_text' => $totals['sub']],
    ];

    if ((float) $totals['tax'] > 0) {
      $form['totals']['tax'] = [
        'label' => ['#plain_text' => t('Tax')],
        'value' => ['#plain_text' => $totals['tax']],
      ];
    }

    if ((float) $totals['discount'] != 0) {
      $form['totals']['discount'] = [
        'label' => ['#plain_text' => t('Discount')],
        'value' => ['#plain_text' => $totals['discount']],
      ];
    }

    $form['totals']['grand'] = [
      'label' => ['#plain_text' => t('Grand Total')],
      'value' => ['#plain_text' => $totals['grand']],
    ];

    $form['coupon'] = [
      '#title' => t('Coupon Code'),
      '#type' => 'textfield',
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['update'] = [
      '#type' => 'submit',
      '#value' => t('Update'),
    ];

    $form['actions']['checkout'] = [
      '#type' => 'submit',
      '#value' => t('Checkout'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    if (!($form_state->getErrors())) {
      $cartFormItems = $form_state->getValue('cart');

      if (!empty($cartFormItems)) {
        $update_cart = [];

        foreach ($cartFormItems as $sku => $item) {
          $update_cart[] = ['sku' => $sku, 'qty' => $item['quantity']];
        }

        $this->cart = $this->cartStorage->getCart();
        $this->cart->setItemsInCart($update_cart);

        $coupon = $form_state->getValue('coupon');

        if (!empty($coupon)) {
          $this->cart->setCoupon($coupon);
        }

        $this->updateCart($form_state);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getTriggeringElement()['#parents'][0] == 'checkout') {
      $form_state->setRedirect('acq_checkout.form');
    }
    else {
      // If we have success message available.
      $msg = !empty($this->successMessage) ? $this->successMessage : $this->t('Your cart has been updated.');
      drupal_set_message($msg);
    }
  }

  /**
   * Cart update utility.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   FormStateInterface object.
   */
  private function updateCart(FormStateInterface $form_state) {
    try {
      if (empty($form_state->getValue('coupon'))) {
        $this->cart->setCoupon('');
      }

      $cart = $this->cartStorage->updateCart();

      $response_message = $cart->get('response_message');
      // We will have type of message like error or success. key '0' contains
      // the response message string while key '1' contains the response
      // message context/type like success or coupon.
      if (!empty($response_message[1])) {
        // If its success.
        if ($response_message[1] == 'success') {
          $this->successMessage = $response_message[0];
        }
        elseif ($response_message[1] == 'error_coupon') {
          // Set the error and require rebuild.
          $form_state->setErrorByName('coupon', $response_message[0]);
          $form_state->setRebuild(TRUE);

          // Remove the coupon and update the cart.
          $this->cart->setCoupon('');
          $this->updateCart($form_state);
        }
      }
    }
    catch (\Exception $e) {
      if (acq_commerce_is_exception_api_down_exception($e)) {
        drupal_set_message($e->getMessage(), 'error');
        $form_state->setErrorByName('custom', $e->getMessage());
        $form_state->setRebuild(TRUE);
      }

      // Dispatch event so action can be taken.
      $dispatcher = \Drupal::service('event_dispatcher');
      $event = new UpdateCartErrorEvent($e);
      $dispatcher->dispatch(UpdateCartErrorEvent::SUBMIT, $event);
    }
  }

}
