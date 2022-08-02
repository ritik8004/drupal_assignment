<?php

namespace Drupal\alshaya_spc\Commands;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drush\Commands\DrushCommands;
use GuzzleHttp\Client;
use Drupal\acq_checkoutcom\CheckoutComAPIWrapper;

/**
 * Class Alshaya Spc Commands.
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
      // phpcs:ignore
      $data = unserialize($payment['data']);
      $type = $data['payment_type'] ?? '';
      if (empty($type)) {
        $type = 'knet';
        if (str_contains($payment['unique_id'], 'pay_tok_')) {
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

      $langcode = $data['langcode'] ?? 'en';

      // Update the store context to match user's language.
      $this->apiWrapper->updateStoreContext($langcode);

      switch ($type) {
        case 'checkout_com':
          $cart_amount = $this->checkoutComApi->getCheckoutAmount($cart['totals']['base_grand_total'], $cart['totals']['quote_currency_code']);
          $payment_info = $this->checkoutComApi->getChargesInfo($payment['unique_id']);
          if (!empty($payment_info['responseCode']) && $payment_info['responseCode'] == '10000') {
            // For checkout.com we get proper response with exact amount user
            // was charged. We need to check again if current amount and paid
            // amount match. For KNET API takes care of validating this amount.
            if ($cart_amount != $payment_info['value']) {
              $this->getLogger('PendingPaymentCheck')->notice('Checkoutcom Payment is complete, but amount does not match. Please check again. Deleting entry now. Cart id: @cart_id, Cart total: @total, Data: @data, Checkoutcom response: @info', [
                '@data' => $payment['data'],
                '@cart_id' => $payment['cart_id'],
                '@info' => json_encode($payment_info),
                '@total' => $cart['totals']['grand_total'],
              ]);

              $this->deletePaymentDataByCartId($payment['cart_id']);

              continue 2;
            }

            try {
              $update = [];
              $update['extension'] = [
                'action' => 'update payment',
                'attempted_payment' => 1,
              ];
              $update['payment'] = [
                'method' => 'checkout_com',
                'additional_data' => [
                  'cko_payment_token' => $payment['unique_id'],
                ],
              ];

              $cart = $this->apiWrapper->updateCart($payment['cart_id'], $update);
              if (empty($cart)) {
                throw new \Exception('Update payment data in cart failed, please check other logs to know the reason');
              }

              $this->placeOrder($update['payment'], $payment['cart_id'], $langcode);

              $this->getLogger('PendingPaymentCheck')->notice('Checkoutcom Payment successful, order placed. Cart id: @cart_id, Data: @data, CheckoutCom response: @info', [
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

            $this->deletePaymentDataByCartId($payment['cart_id']);
          }
          elseif (!empty($payment_info['responseCode']) && $payment_info['responseCode'] != '10000') {
            $this->getLogger('PendingPaymentCheck')->notice('Checkoutcom Payment failed. Deleting entry now. Cart id: @cart_id, Cart total: @total, Data: @data, Checkoutcom response: @info', [
              '@data' => $payment['data'],
              '@cart_id' => $payment['cart_id'],
              '@info' => json_encode($payment_info),
            ]);

            $this->deletePaymentDataByCartId($payment['cart_id']);
          }
          elseif (empty($payment_info['id'])) {
            $this->getLogger('PendingPaymentCheck')->notice('Checkoutcom Payment callback requested with empty token. Cart id: @cart_id, Cart total: @total, Data: @data, Checkoutcom response: @info', [
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
   * @param array $update
   *   Update Array.
   * @param string $cart_id
   *   Cart id.
   * @param string $langcode
   *   Language code.
   *
   * @throws \Exception
   */
  protected function placeOrder(array $update, string $cart_id, $langcode) {
    $request_options = [];
    $request_options['query']['lang'] = $langcode;

    // Add a custom header to ensure Middleware allows this request
    // without further authentication.
    $request_options['json']['data']['paymentMethod'] = $update;
    $request_options['json']['cart_id'] = $cart_id;
    $request_options['headers']['alshaya-middleware'] = md5(Settings::get('middleware_auth'));
    $endpoint = 'middleware/public/cart/place-order-system';
    $response = $this->createClient()->post($endpoint, $request_options);
    $result = json_decode($response->getBody()->getContents(), TRUE);
    if (empty($result['success'])) {
      throw new \Exception($result['error_message'] ?? 'Unknown error');
    }
  }

}
