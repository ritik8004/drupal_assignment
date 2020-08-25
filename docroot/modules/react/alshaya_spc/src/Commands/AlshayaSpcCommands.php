<?php

namespace Drupal\alshaya_spc\Commands;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\alshaya_knet\Knet\KnetApiWrapper;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drush\Commands\DrushCommands;
use GuzzleHttp\Client;
use Drupal\acq_checkoutcom\CheckoutComAPIWrapper;

/**
 * Class AlshayaSpcCommands.
 *
 * @package Drupal\alshaya_spc\Commands
 */
class AlshayaSpcCommands extends DrushCommands {

  use LoggerChannelTrait;

  /**
   * Alshaya Magento API Wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Checkout.com API Wrapper.
   *
   * @var \Drupal\acq_checkoutcom\CheckoutComAPIWrapper
   */
  protected $checkoutComApi;

  /**
   * AlshayaSpcCommands constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Alshaya Magento API Wrapper.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection.
   * @param \Drupal\acq_checkoutcom\CheckoutComAPIWrapper $checkout_com_api
   *   Checkout.com API Wrapper.
   */
  public function __construct(AlshayaApiWrapper $api_wrapper,
                              Connection $connection,
                              CheckoutComAPIWrapper $checkout_com_api) {
    $this->apiWrapper = $api_wrapper;
    $this->connection = $connection;
    $this->checkoutComApi = $checkout_com_api;
  }

  /**
   * Attempt to reconcile payment transaction logs for the provided methods.
   *
   * Log the cases where the transaction has not been concluded within the
   * time specified.
   *
   * @param string $methods
   *   Comma separated list of payment methods to check for.
   *
   * @command alshaya_spc:check-pending-payments
   *
   * @aliases alshaya-check-pending-payments
   *
   * @usage alshaya-check-pending-payments knet
   *   Checks for all the pending payments for payment method KNET.
   * @usage alshaya-check-pending-payments 'knet,checkout_com'
   *   Checks for all the pending payments for payment method KNET
   *   and checkout.com.
   */
  public function checkPendingPayments(string $methods) {
    $times = Settings::get('alshaya_checkout_settings')['pending_payments'];

    $methods = explode(',', $methods);

    $query = $this->connection->select('middleware_payment_data');
    $query->fields('middleware_payment_data');
    $query->condition('timestamp', strtotime('-' . $times['before'] . ' seconds'), '<');
    $query->condition('timestamp', strtotime('-' . $times['after'] . ' seconds'), '>');
    $query->orderBy('timestamp', 'DESC');
    $payments = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($payments as $payment) {
      $data = unserialize($payment['data']);
      $type = $data['payment_type'] ?? '';
      if (empty($type)) {
        $type = 'knet';
        if (strpos($payment['unique_id'], 'pay_tok_') !== FALSE) {
          $type = 'checkout_com';
        }
      }

      if (!in_array($type, $methods)) {
        continue;
      }

      try {
        // First try to get cart data.
        $cart = $this->apiWrapper->getCart($payment['cart_id']);

        if ($cart === 'false') {
          throw new \Exception('Cart no longer available', 404);
        }

        $cart = json_decode($cart, TRUE);
      }
      catch (\Exception $e) {
        $message = 'Not able to get cart for id @cart_id, exception: @code @message, payment data: @data';
        if ($e->getCode() == 404) {
          $message = 'Cart no longer available for id @cart_id, deleting payment data @data';
        }

        $this->getLogger('PendingPaymentCheck')->warning($message, [
          '@code' => $e->getCode(),
          '@message' => $e->getMessage(),
          '@cart_id' => $payment['cart_id'],
          '@data' => $payment['data'],
        ]);

        $this->deletePaymentDataByCartId($payment['cart_id']);

        continue;
      }

      if (empty($cart)) {
        continue;
      }

      switch ($type) {
        case 'knet':
          // Update the store context to match user's language.
          $this->apiWrapper->updateStoreContext($data['langcode']);

          $credentials = Settings::get('knet');
          $wrapper = new KnetApiWrapper(
            Settings::get('alshaya_knet.settings')['knet_base_url'],
            $credentials['tranportal_id'],
            $credentials['tranportal_password']
          );

          $info = $wrapper->getTransactionInfoByTrackingId(
            $data['order_id'],
            $cart['totals']['grand_total']
          );

          $status = strtolower($info['result'] ?? '');

          if (in_array($status, ['success', 'captured'])) {
            try {
              // Set the values in keys as expected by Magento.
              $info['payment_id'] = $info['paymentid'];
              $info['post_date'] = $info['postdate'];
              $info['transaction_id'] = $info['tranid'] ?? '';
              $info['auth_code'] = $info['auth'];
              $info['tracking_id'] = $info['trackid'];
              $info['customer_id'] = (int) $info['udf2'];
              $info['quote_id'] = (int) $info['udf3'];

              $update = [];
              $update['payment'] = [
                'method' => 'knet',
                'additional_data' => $info,
              ];
              $update['extension'] = [
                'action' => 'update payment',
                'attempted_payment' => 1,
              ];

              $cart = $this->apiWrapper->updateCart($payment['cart_id'], $update);
              if (empty($cart)) {
                throw new \Exception('Update payment data in cart failed, please check other logs to know the reason');
              }

              // Add a custom header to ensure Middleware allows this request
              // without further authentication.
              $request_options['json']['data']['paymentMethod'] = $update['payment'];
              $request_options['json']['cart_id'] = $payment['cart_id'];
              $request_options['headers']['alshaya-middleware'] = md5(Settings::get('middleware_auth'));

              $endpoint = 'middleware/public/cart/place-order-system';
              $response = $this->createClient()->post($endpoint, $request_options);
              $result = json_decode($response->getBody()->getContents(), TRUE);
              if (empty($result['success'])) {
                throw new \Exception($result['error_message'] ?? 'Unknown error');
              }

              $this->getLogger('PendingPaymentCheck')->notice('KNET Payment successful, order placed. Cart id: @cart_id, Data: @data, KNET response: @info', [
                '@data' => $payment['data'],
                '@cart_id' => $payment['cart_id'],
                '@info' => json_encode($info),
              ]);
            }
            catch (\Exception $e) {
              $this->getLogger('PendingPaymentCheck')->notice('KNET Payment successful, order failed. Cart id: @cart_id, Data: @data, KNET response: @info, Exception: @exception', [
                '@data' => $payment['data'],
                '@cart_id' => $payment['cart_id'],
                '@info' => json_encode($info),
                '@exception' => $e->getMessage(),
              ]);
            }

            // If place order attempted and failed, add log with exception.
            $this->deletePaymentDataByCartId($payment['cart_id']);
          }
          elseif (strpos($status, 'transaction not found') !== FALSE) {
            $this->getLogger('PendingPaymentCheck')->notice('KNET Payment transaction not found, which means user cancelled. Deleting entry now. Cart id: @cart_id, Cart total: @total, Data: @data, KNET response: @info', [
              '@data' => $payment['data'],
              '@cart_id' => $payment['cart_id'],
              '@info' => json_encode($info),
            ]);

            $this->deletePaymentDataByCartId($payment['cart_id']);
          }
          elseif (strpos($status, 'not captured') !== FALSE) {
            $this->getLogger('PendingPaymentCheck')->notice('KNET Payment failed. Deleting entry now. Cart id: @cart_id, Cart total: @total, Data: @data, KNET response: @info', [
              '@data' => $payment['data'],
              '@cart_id' => $payment['cart_id'],
              '@info' => json_encode($info),
            ]);

            $this->deletePaymentDataByCartId($payment['cart_id']);
          }
          elseif ($status && $info['amt'] != $cart['totals']['grand_total']) {
            $this->getLogger('PendingPaymentCheck')->notice('KNET Payment is possibly complete, but it seems amount does not match. Please check again. Deleting entry now. Cart id: @cart_id, Cart total: @total, Data: @data, KNET response: @info', [
              '@data' => $payment['data'],
              '@cart_id' => $payment['cart_id'],
              '@info' => json_encode($info),
              '@total' => $cart['totals']['grand_total'],
            ]);

            $this->deletePaymentDataByCartId($payment['cart_id']);
          }
          elseif ($status && $payment['timestamp'] < strtotime('-1 hour')) {
            $this->getLogger('PendingPaymentCheck')->notice('KNET Payment not complete, deleting entry as it is already 1 hour now. Cart id: @cart_id, Data: @data, KNET response: @info', [
              '@data' => $payment['data'],
              '@cart_id' => $payment['cart_id'],
              '@info' => json_encode($info),
            ]);

            $this->deletePaymentDataByCartId($payment['cart_id']);
          }
          else {
            $this->getLogger('PendingPaymentCheck')->notice('KNET Payment not complete or info not available, not deleting entry to retry later. Cart id: @cart_id, Data: @data, KNET response: @info', [
              '@data' => $payment['data'],
              '@cart_id' => $payment['cart_id'],
              '@info' => json_encode($info),
            ]);
          }

          break;

        case 'checkout_com':
          $cart_amount = $this->checkoutComApi->getCheckoutAmount($cart['totals']['base_grand_total'], $cart['totals']['quote_currency_code']);
          $payment_info = $this->checkoutComApi->getChargesInfo($payment['unique_id']);
          if ($payment_info['responseMessage'] == 'Approved') {
            try {
              $payment_data = [
                'method' => 'checkout_com',
                'additional_data' => [
                  'cko_payment_token' => $payment['unique_id'],
                ],
              ];
              // Push the additional data to cart.
              $payment_updated = $this->updatePayment($payment_data, $payment['cart_id'], ['attempted_payment' => 1]);
              if (empty($payment_updated) || !empty($payment_updated['error'])) {
                throw new \Exception($payment_updated['error_message'], $payment_updated['error_code']);
              }

              // Place order.
              $order = $this->placeOrder(['paymentMethod' => $payment_data], $payment['cart_id']);
              if (empty($order) || !empty($order['error'])) {
                throw new \Exception($order['error_message'] ?? 'Place order failed', $order['error_code'] ?? 500);
              }

              $this->getLogger('PendingPaymentCheck')->notice('Checkout com Payment successful, order placed. Cart id: @cart_id, Data: @data, CheckoutCom response: @info', [
                '@data' => $payment['data'],
                '@cart_id' => $payment['cart_id'],
                '@info' => json_encode($payment_info),
              ]);
            }
            catch (\Exception $e) {
              $this->getLogger('PendingPaymentCheck')->notice('Checkoutcom Payment successful, order failed. Cart id: @cart_id, Data: @data, Checkoutcom response: @info, Exception: @exception', [
                '@data' => $payment['data'],
                '@cart_id' => $payment['cart_id'],
                '@info' => json_encode($payment_info),
                '@exception' => $e->getMessage(),
              ]);
            }

            // $this->deletePaymentDataByCartId($payment['cart_id']);.
          }
          elseif ($cart_amount != $payment_info['value']) {
            $this->getLogger('PendingPaymentCheck')->notice('Checkoutcom Payment is possibly complete, but it seems amount does not match. Please check again. Deleting entry now. Cart id: @cart_id, Cart total: @total, Data: @data, Checkoutcom response: @info', [
              '@data' => $payment['data'],
              '@cart_id' => $payment['cart_id'],
              '@info' => json_encode($payment_info),
              '@total' => $cart['totals']['grand_total'],
            ]);

            $this->deletePaymentDataByCartId($payment['cart_id']);
          }
          else {
            $this->getLogger('PendingPaymentCheck')->notice('Checkoutcom Payment not complete or info not available, not deleting entry to retry later. Cart id: @cart_id, Data: @data, Checkoutcom response: @info', [
              '@data' => $payment['data'],
              '@cart_id' => $payment['cart_id'],
              '@info' => json_encode($payment_info),
            ]);
          }
      }
    }
  }

  /**
   * Delete Payment Data by Cart ID.
   *
   * @param string $cart_id
   *   Cart ID.
   */
  protected function deletePaymentDataByCartId(string $cart_id) {
    $this->connection->delete('middleware_payment_data')
      ->condition('cart_id', $cart_id)
      ->execute();
  }

  /**
   * Crate a new client object.
   *
   * Create a Guzzle http client configured to connect to the
   * same site instance.
   *
   * @return \GuzzleHttp\Client
   *   Object of initialized client.
   */
  protected function createClient() {
    $url = Url::fromRoute('<current>')->setAbsolute()->toString();

    $configuration = [
      'base_uri' => 'https://' . parse_url($url, PHP_URL_HOST),
      'verify'   => FALSE,
    ];

    return (new Client($configuration));
  }

  /**
   * Adding payment on the cart.
   *
   * @param array $data
   *   Payment info.
   * @param string $cart_id
   *   Cart id.
   * @param array $extension
   *   (Optional) Cart extension.
   *
   * @return array
   *   Cart data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function updatePayment(array $data, string $cart_id, array $extension = []) {
    $extension['action'] = 'update payment';

    $update = [
      'extension' => (object) $extension,
    ];

    $update['payment'] = [
      'method' => $data['method'],
      'additional_data' => $data['additional_data'],
    ];

    $expire = (int) $_ENV['CACHE_TIME_LIMIT_PAYMENT_METHOD_SELECTED'];
    if ($expire > 0) {
      $this->cache->set('payment_method', $expire, $data['method']);
    }

    $old_cart = $this->apiWrapper->getCart($cart_id);
    $cart = $this->apiWrapper->updateCart($cart_id, $update);
    if (isset($cart['error_code'])) {
      $error_message = $cart['error_code'] > 600
        ? 'Back-end system is down'
        : $cart['error_message'];

      $this->getLogger('PendingPaymentCheck')->notice('Checkoutcom Payment cart failure, not deleting entry to retry later. Cart id: @cart_id, Data: @data, Checkoutcom response: @info, Error: @error', [
        '@data' => json_encode($cart),
        '@cart_id' => $cart_id,
        '@info' => json_encode($old_cart),
        '@error' => $error_message,
      ]);
    }

    return $cart;
  }

  /**
   * Place order.
   *
   * @param array $data
   *   Post data.
   * @param string $cart_id
   *   Post data.
   *
   * @return array
   *   Status.
   */
  public function placeOrder(array $data, string $cart_id) {
    // $url = sprintf('carts/%d/order', $cart_id);.
    $cart = $this->apiWrapper->getCart($cart_id);
    $shipping_address = $cart['extension_attributes']['shipping_assignments']['shipping']['address'];
    $shipping_method = $shipping_address['method'];

    // Check if shiping method is present else throw error.
    if (empty($shipping_method)) {
      $this->getLogger('PendingPaymentCheck')->notice('Checkoutcom Payment error while placing order. Cart id: @cart_id, Cart: @cart', [
        '@cart_id' => $cart_id,
        '@cart' => json_encode($cart),
      ]);
      return $this->deletePaymentDataByCartId($cart_id);
    }

    // Check if shipping address not have custom attributes.
    if (empty($shipping_address['custom_attributes'])) {
      $this->getLogger('PendingPaymentCheck')->notice('Checkoutcom Payment error while placing order. Cart id: @cart_id, Cart: @cart', [
        '@cart_id' => $cart_id,
        '@cart' => json_encode($cart),
      ]);
      return $this->deletePaymentDataByCartId($cart_id);
    }

    // If first/last name not available in shipping address.
    if (empty($shipping_address['firstname'])
      || empty($shipping_address['lastname'])) {
      $this->getLogger('PendingPaymentCheck')->notice('Checkoutcom Payment error while placing order. Cart id: @cart_id, Cart: @cart', [
        '@cart_id' => $cart_id,
        '@cart' => json_encode($cart),
      ]);
      return $this->deletePaymentDataByCartId($cart_id);
    }

    return $this->deletePaymentDataByCartId($cart_id);
  }

}
