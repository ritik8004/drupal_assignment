<?php

namespace Drupal\alshaya_acm\Controller;

use Drupal\alshaya_acm\CartHelper;
use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\acq_cart\CartInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\acq_cart\CartStorageInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class CartController.
 */
class CartController extends ControllerBase {

  /**
   * Drupal\acq_cart\CartStorageInterface definition.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * Drupal\acq_cart\Cart definition.
   *
   * @var \Drupal\acq_cart\Cart
   */
  protected $cart;

  /**
   * CSRF Token generator object.
   *
   * @var \Drupal\Core\Access\CsrfTokenGenerator
   */
  protected $csrfTokenGenerator;

  /**
   * Cart Helper.
   *
   * @var \Drupal\alshaya_acm\CartHelper
   */
  private $cartHelper;

  /**
   * Constructor.
   *
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   The cart storage.
   * @param \Drupal\Core\Access\CsrfTokenGenerator $csrf_token
   *   CSRF Token generator object.
   * @param \Drupal\alshaya_acm\CartHelper $cart_helper
   *   Cart Helper.
   */
  public function __construct(CartStorageInterface $cart_storage,
                              CsrfTokenGenerator $csrf_token,
                              CartHelper $cart_helper) {
    $this->cartStorage = $cart_storage;
    $this->cart = $this->cartStorage->getCart();
    $this->csrfTokenGenerator = $csrf_token;
    $this->cartHelper = $cart_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acq_cart.cart_storage'),
      $container->get('csrf_token'),
      $container->get('alshaya_acm.cart_helper')
    );
  }

  /**
   * Handler for cart/remove/{sku}.
   */
  public function cartRemoveSku($sku, $token, $js, $coupon) {
    if (!empty($sku) && $this->cart instanceof CartInterface) {
      $token_value = $this->cart->id() . '/' . $sku;
      if (!$this->csrfTokenGenerator->validate($token, $token_value)) {
        throw new AccessDeniedHttpException();
      }

      // We use encoded string to handle cases like "MHHW0629 1 6/7Y".
      $sku = base64_decode($sku);

      // If there is a coupon applied on cart.
      if (!empty($this->cart->getCoupon())) {
        // If only one item in cart.
        if (count($this->cart->items()) == 1) {
          // Remove coupon.
          $this->cart->setCoupon('');
        }
        elseif ($coupon === 'remove') {
          $this->cart->setCoupon('');
        }
      }

      try {
        // Remove the item from cart.
        $this->cartHelper->removeItemFromCart($sku);

        // Removal was successful in first/second try. We show success message.
        $this->messenger()->addStatus($this->t('The product has been removed from your cart.'));
      }
      catch (\Exception $e) {
        // Do nothing, we have stored logs for the failure and we will display
        // nothing (no-change) to the user.
      }

      if ($js === 'ajax') {
        $response = new AjaxResponse();
        $response->addCommand(new InvokeCommand(NULL, 'removeCartItem', [$sku]));
        return $response;
      }
    }
    else {
      drupal_set_message($this->t('Oops, something went wrong.'), 'error');

      if ($js === 'ajax') {
        $response = new AjaxResponse();
        $response->addCommand(new RedirectCommand(Url::fromRoute('acq_cart.cart')->toString()));
        return $response;
      }
    }

    return $this->redirect('acq_cart.cart');
  }

}
