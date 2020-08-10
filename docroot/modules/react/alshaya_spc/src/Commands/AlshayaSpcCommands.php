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
   * AlshayaSpcCommands constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Alshaya Magento API Wrapper.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection.
   */
  public function __construct(AlshayaApiWrapper $api_wrapper,
                              Connection $connection) {
    $this->apiWrapper = $api_wrapper;
    $this->connection = $connection;
  }

  /**
   * Attempt to reconcile payment transaction logs for the provided methods.
   *
   * Log the cases where the transaction has not been concluded within the
   * time specified.
   *
   * @param string $methods
   *   Comma separated list of payment methods to check for.
   * @param array $options
   *   Options supported with drush command.
   *
   * @command alshaya_spc:check-pending-payments
   *
   * @options seconds-old Check for payments which are at-least X seconds old.
   *
   * @aliases alshaya-check-pending-payments
   *
   * @usage alshaya-check-pending-payments
   *   Checks for all the pending payments (for all payment types).
   * @usage alshaya-check-pending-payments knet
   *   Checks for all the pending payments for payment method KNET.
   * @usage alshaya-check-pending-payments 'knet,checkout_com'
   *   Checks for all the pending payments for payment method KNET
   *   and checkout.com.
   * @usage alshaya-check-pending-payments --seconds-old=240
   *   Checks for all the pending payments which are 4 minutes old. This means
   *   it will check for all the payments for which user has not returned back
   *   to Drupal in last 4 minutes. Default is 500 seconds (8min 20sec).
   */
  public function checkPendingPayments(string $methods = 'knet', array $options = ['seconds-old' => 500]) {
    $methods = explode(',', $methods);

    $seconds = (int) $options['seconds-old'];
    $query = $this->connection->select('middleware_payment_data');
    $query->fields('middleware_payment_data');
    $query->condition('timestamp', strtotime("-${seconds} seconds"), '<');
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
        if ($e->getCode() == 404) {
          $this->getLogger('PendingPaymentCheck')->notice('Cart no longer available for id @cart_id, deleting payment data @data', [
            '@data' => $payment['data'],
            '@cart_id' => $payment['cart_id'],
          ]);

          $this->deletePaymentDataByCartId($payment['cart_id']);

          continue;
        }

        // Probably Magento is down right now or something else wrong.
        // Do not delete payment data and just add log message.
        $this->getLogger('PendingPaymentCheck')->warning('Not able to get cart for id @cart_id, exception: @code @message, payment data: @data', [
          '@code' => $e->getCode(),
          '@message' => $e->getMessage(),
          '@cart_id' => $payment['cart_id'],
          '@data' => $payment['data'],
        ]);

        continue;
      }

      if (empty($cart)) {
        continue;
      }

      switch ($type) {
        case 'knet':
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
          elseif ($status && $payment['timestamp'] < strtotime('-1 day')) {
            $this->getLogger('PendingPaymentCheck')->notice('KNET Payment not complete, deleting entry as it is already one day now. Cart id: @cart_id, Data: @data, KNET response: @info', [
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

}
