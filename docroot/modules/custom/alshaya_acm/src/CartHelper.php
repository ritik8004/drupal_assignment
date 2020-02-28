<?php

namespace Drupal\alshaya_acm;

use Drupal\acq_cart\Cart;
use Drupal\acq_cart\CartInterface;
use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\acq_commerce\Response\NeedsRedirectException;
use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\AddToCartErrorEvent;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType\Configurable;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * ApiHelper.
 */
class CartHelper {

  use MessengerTrait;
  use StringTranslationTrait;

  /**
   * The cart storage service.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * Event Dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * Module Handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * API Wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructor.
   *
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   Cart Storage service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   Event Dispatcher.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   API Wrapper.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel
   *   Logger Factory.
   */
  public function __construct(CartStorageInterface $cart_storage,
                              EventDispatcherInterface $dispatcher,
                              ModuleHandlerInterface $module_handler,
                              AlshayaApiWrapper $api_wrapper,
                              ConfigFactoryInterface $config_factory,
                              LoggerChannelFactoryInterface $logger_channel) {
    $this->cartStorage = $cart_storage;
    $this->dispatcher = $dispatcher;
    $this->moduleHandler = $module_handler;
    $this->apiWrapper = $api_wrapper;
    $this->configFactory = $config_factory;
    $this->logger = $logger_channel->get('CartHelper');
  }

  /**
   * Get the cart object.
   *
   * @return \Drupal\acq_cart\CartInterface
   *   The cart object.
   */
  public function getCart() {
    return $this->cartStorage->getCart(FALSE);
  }

  /**
   * Wrapper function to get cleaned shipping address.
   *
   * @param \Drupal\acq_cart\CartInterface|null $cart
   *   Cart object.
   *
   * @return array
   *   Payment methods.
   */
  public function getShipping(CartInterface $cart = NULL) {
    if (empty($cart)) {
      return [];
    }

    return $cart->getAddressArray($cart->getShipping());
  }

  /**
   * Wrapper function to get cleaned billing address.
   *
   * @param \Drupal\acq_cart\CartInterface|null $cart
   *   Cart object.
   *
   * @return array
   *   Payment methods.
   */
  public function getBilling(CartInterface $cart = NULL) {
    if (empty($cart)) {
      return [];
    }

    return $cart->getAddressArray($cart->getBilling());
  }

  /**
   * Get magento address as array.
   *
   * @param mixed $address
   *   Address object or array.
   *
   * @return array
   *   Processed address array.
   */
  public function getAddressArray($address) {
    // Convert this to array, we always deal with arrays in our custom code.
    if (is_object($address)) {
      $address = (array) $address;
    }

    // Empty check.
    if (empty($address['country_id'])) {
      return [];
    }

    // Convert extension too.
    if (isset($address['extension']) && is_object($address['extension'])) {
      $address['extension'] = (array) $address['extension'];
    }

    return $address;
  }

  /**
   * Get clean cart to log.
   *
   * @param \Drupal\acq_cart\Cart|object|array $cart
   *   Cart object to clean.
   *
   * @return string
   *   Cleaned cart data as JSON string.
   */
  public function getCleanCartToLog($cart) {
    if ($cart instanceof Cart) {
      return $cart->getDataToLog();
    }

    $cartData = is_object($cart) ? (array) $cart : $cart;

    $shipping = $this->getAddressArray($cartData['shipping']);

    // Billing is not required for debugging.
    unset($cartData['billing']);

    // We will remove all at root level.
    // We will leave fields in extension here.
    unset($cartData['shipping']);
    $cartData['shipping']['extension'] = $shipping['extension'];

    return json_encode($cartData);
  }

  /**
   * Remove out of stock items from cart (in session only).
   *
   * @return bool
   *   TRUE if any item removed.
   */
  public function removeOutOfStockItemsFromCart(): bool {
    $removed = FALSE;
    $cart = $this->cartStorage->getCart(FALSE);

    // Sanity check.
    if (empty($cart)) {
      return $removed;
    }

    $items = $cart->items();

    foreach ($items as $index => $item) {
      $sku = SKU::loadFromSku($item['sku']);
      $plugin = $sku->getPluginInstance();
      if (!$plugin->isProductInStock($sku)) {
        $removed = TRUE;
        unset($items[$index]);
      }
    }

    if ($removed) {
      $cart->setItemsInCart($items);
    }

    return $removed;
  }

  /**
   * Wrapper function to remove item from cart.
   *
   * Tries to remove all other OOS items as well if required.
   *
   * @param string $sku
   *   SKU to remove.
   *
   * @throws \Drupal\acq_commerce\Response\NeedsRedirectException
   */
  public function removeItemFromCart(string $sku) {
    $cart = $this->cartStorage->getCart(FALSE);
    $cart->removeItemFromCart($sku);

    try {
      $this->updateCartWrapper(__METHOD__);
    }
    catch (\Exception $e) {
      // Try to remove again (only once) after removing OOS items.
      if ($this->removeOutOfStockItemsFromCart()) {
        $cart = $this->cartStorage->getCart(FALSE);
        $cart->removeItemFromCart($sku);
        $this->updateCartWrapper(__METHOD__);

        // Operation was successful after second try, show the error message
        // for user to know about the updates user didn't ask for.
        $this->messenger()->addError($this->t('Sorry, one or more products in your basket are no longer available and have been removed in order to proceed.'));
      }
    }
  }

  /**
   * Wrapper function to update cart and handle exception.
   *
   * @param string $function
   *   Function name invoking update cart for logs.
   *
   * @throws \Drupal\acq_commerce\Response\NeedsRedirectException
   */
  public function updateCartWrapper(string $function) {
    $cart = $this->cartStorage->getCart(FALSE);

    if (empty($cart)) {
      throw new NeedsRedirectException(Url::fromRoute('acq_cart.cart')->toString());
    }

    try {
      $this->cartStorage->updateCart(FALSE);
    }
    catch (\Exception $e) {
      $this->logger->error('Error while updating cart @cart_id, invoked from @function, exception: @message', [
        '@message' => $e->getMessage(),
        '@cart_id' => $cart->id(),
        '@function' => $function,
      ]);

      if (_alshaya_acm_is_out_of_stock_exception($e)) {
        if ($cart = $this->cartStorage->getCart(FALSE)) {
          $this->refreshStockForProductsInCart($cart);
          $cart->setCheckoutStep('');
        }
      }

      throw new NeedsRedirectException(Url::fromRoute('acq_cart.cart')->toString());
    }
  }

  /**
   * Refresh stock cache and Drupal cache of products in cart.
   *
   * @param \Drupal\acq_cart\CartInterface|null $cart
   *   Cart.
   */
  public function refreshStockForProductsInCart(CartInterface $cart = NULL) {
    $parent_skus = [];

    if (empty($cart)) {
      $cart = $this->cartStorage->getCart(FALSE);
    }

    // Still if empty, simply return.
    if (empty($cart)) {
      return;
    }

    foreach ($cart->items() ?? [] as $item) {
      if ($sku_entity = SKU::loadFromSku($item['sku'])) {
        /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
        $plugin = $sku_entity->getPluginInstance();
        $current_parent = $plugin->getParentSku($sku_entity);
        $current_parent_sku = $current_parent
          ? $current_parent->getSku() : '';

        // Refresh Current Sku stock.
        $sku_entity->refreshStock();
        // Refresh parent stock once if exists for cart items.
        if ($current_parent_sku && !in_array($current_parent_sku, $parent_skus)) {
          $parent_skus[] = $current_parent_sku;
          $current_parent->refreshStock();
        }
      }
    }
  }

  /**
   * Add item to cart.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU Entity.
   * @param array $data
   *   Post data.
   *
   * @return bool|string
   *   TRUE if successful, error message otherwise.
   */
  public function addItemToCart(SKU $sku, array $data) {
    try {
      $cart = $this->cartStorage->getCart(TRUE);
      if (empty($cart)) {
        $e = new \Exception(acq_commerce_api_down_global_error_message(), APIWrapper::API_DOWN_ERROR_CODE);

        // Dispatch event so action can be taken.
        $this->dispatcher->dispatch(AddToCartErrorEvent::SUBMIT, new AddToCartErrorEvent($e));

        throw $e;
      }
    }
    catch (\Exception $e) {
      return $e->getMessage();
    }

    $quantity = $data['quantity'] ?? 1;
    $quantity = (int) $quantity;

    if (empty($quantity)) {
      throw new \InvalidArgumentException('Quantity is required.');
    }

    switch ($sku->bundle()) {
      case 'configurable':

        $selected_variant_sku = $data['selected_variant_sku'] ?? '';
        if (empty($selected_variant_sku)) {
          throw new \InvalidArgumentException('Selected Variant SKU is required.');
        }

        $variant = SKU::loadFromSku($selected_variant_sku);
        if (!($variant instanceof SKUInterface)) {
          throw new \InvalidArgumentException('Invalid selected variant: ' . $selected_variant_sku);
        }

        // If selected parent sku available in post data and that is not
        // same as available parent variant.
        if (!empty($data['selected_parent_sku'])
          && $sku->getSku() != $data['selected_parent_sku']) {
          $sku = SKU::loadFromSku($data['selected_parent_sku']);
          if (!($sku instanceof SKUInterface)) {
            throw new \InvalidArgumentException('Unable to load parent:' . $data['selected_parent_sku'] . ' for selected variant: ' . $selected_variant_sku);
          }
        }

        if ($cart->hasItem($variant->getSku())) {
          $cart->addItemToCart($variant->getSku(), $quantity);
        }
        else {
          $tree = Configurable::deriveProductTree($sku);

          foreach ($data['configurables'] as $code => $value) {
            $options[] = [
              'option_id' => $tree['configurables'][$code]['attribute_id'],
              'option_value' => $value,
            ];
          }

          // Allow other modules to update the options info sent to ACM.
          $this->moduleHandler->alter('acq_sku_configurable_cart_options', $options, $sku);

          $cart->addRawItemToCart([
            'name' => $sku->label(),
            'sku' => $sku->getSku(),
            'qty' => $quantity,
            'options' => [
              'configurable_item_options' => $options,
            ],
          ]);
        }

        break;

      default:
        // We might be adding different product then the visited PDP.
        // Use the product SKU from request data if available.
        $selected_variant_sku = $data['selected_variant_sku'] ?? '';
        $sku = empty($selected_variant_sku) ? $sku : SKU::loadFromSku($selected_variant_sku);
        if (!($sku instanceof SKUInterface)) {
          throw new \InvalidArgumentException('Invalid selected variant: ' . $selected_variant_sku);
        }
        $cart->addItemToCart($sku->getSku(), $quantity);
        break;
    }

    try {
      $this->cartStorage->updateCart(FALSE);
    }
    catch (\Exception $e) {
      if (isset($variant)) {
        $plugin = $variant->getPluginInstance();
        $plugin->refreshStock($variant);
      }

      $plugin = $sku->getPluginInstance();
      $plugin->refreshStock($sku);

      // Dispatch event so action can be taken.
      $this->dispatcher->dispatch(AddToCartErrorEvent::SUBMIT, new AddToCartErrorEvent($e));
      return $e->getMessage();
    }

    return TRUE;
  }

  /**
   * Cancel cart reservation is required.
   *
   * @param string $message
   *   Message to log for cancelling reservation.
   */
  public function cancelCartReservation(string $message) {
    $cart = $this->cartStorage->getCart(FALSE);

    if (!($cart instanceof CartInterface)) {
      return;
    }

    if ($this->isCancelReservationEnabled() && !empty($cart->getExtension('attempted_payment'))) {
      try {
        $response = $this->apiWrapper->cancelCartReservation((string) $cart->id(), $message);
        if (empty($response['status']) || $response['status'] !== 'SUCCESS') {
          throw new \Exception($response['message'] ?? Json::encode($response));
        }
      }
      catch (\Exception $e) {
        $this->logger->warning('Error occurred while cancelling reservation for cart id @cart_id, Drupal message: @message, API Response: @response', [
          '@cart_id' => $cart->id(),
          '@message' => $message,
          '@response' => $e->getMessage(),
        ]);
      }

      // Get current step, we will set it again after restore.
      $step = $cart->getCheckoutStep();

      // Restore cart to get more info about what is wrong in cart.
      $this->cartStorage->restoreCart($cart->id());

      // Restore current step if cart still available.
      $cart = $this->cartStorage->getCart(FALSE);
      if ($cart instanceof CartInterface) {
        $cart->setCheckoutStep($step);
      }
    }
  }

  /**
   * Wrapper to get the flag if cancel reservation API is enabled or not.
   *
   * @return int|bool
   *   Flag value.
   */
  protected function isCancelReservationEnabled() {
    return $this->configFactory->get('alshaya_acm_checkout.settings')->get('cancel_reservation_enabled') ?? 0;
  }

}
