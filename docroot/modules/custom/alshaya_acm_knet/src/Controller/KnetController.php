<?php

namespace Drupal\alshaya_acm_knet\Controller;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class CheckoutSettingsForm.
 */
class KnetController extends ControllerBase {

  /**
   * APIWrapper object.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  protected $apiWrapper;

  /**
   * Array containing knet settings from config.
   *
   * @var array
   */
  protected $knetSettings;

  /**
   * Constructor.
   *
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   APIWrapper object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Factory object.
   */
  public function __construct(APIWrapper $api_wrapper, ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger_factory) {
    $this->apiWrapper = $api_wrapper;
    $this->knetSettings = $config_factory->get('alshaya_acm_knet.settings');
    $this->logger = $logger_factory->get('alshaya_acm_knet');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acq_commerce.api'),
      $container->get('config.factory'),
      $container->get('logger.factory')
    );
  }

  /**
   * Page callback to process the payment and return redirect URL.
   */
  public function response() {
    $response['payment_id'] = $_POST['paymentid'];
    $response['result'] = $_POST['result'];
    $response['post_date'] = $_POST['postdate'];
    $response['transaction_id'] = $_POST['tranid'];
    $response['auth'] = $_POST['auth'];
    $response['ref'] = $_POST['ref'];
    $response['tracking_id'] = $_POST['trackid'];
    $response['user_id'] = $_POST['udf1'];
    $response['customer_id'] = $_POST['udf2'];
    $response['order_id'] = $_POST['udf3'];
    $response['email'] = $_POST['udf4'];

    $state_key = md5($response['order_id']);
    \Drupal::state()->set($state_key, $response);

    $result_url = 'REDIRECT=';

    if ($response['result'] == 'CAPTURED') {
      $result_url .= Url::fromRoute('alshaya_acm_knet.success', ['state_key' => $state_key], ['absolute' => TRUE])->toString();

      $this->logger->info('KNET update for @order_id: @result_url @message', [
        '@order_id' => $response['order_id'],
        '@result_url' => $result_url,
        '@message' => json_encode($response),
      ]);
    }
    else {
      $result_url .= Url::fromRoute('alshaya_acm_knet.error', ['state_key' => $state_key], ['absolute' => TRUE])->toString();

      $this->logger->error('KNET update for @order_id: @result_url @message', [
        '@order_id' => $response['order_id'],
        '@result_url' => $result_url,
        '@message' => json_encode($response),
      ]);
    }

    print $result_url;
    exit;
  }

  /**
   * Page callback for success state.
   */
  public function success($state_key) {
    $data = \Drupal::state()->get($state_key);

    if (empty($data)) {
      return new AccessDeniedHttpException();
    }

    $message = '';

    foreach ($data as $key => $value) {
      $message .= $key . ': ' . $value . PHP_EOL;
    }

    $this->logger->info('KNET payment complete for @order_id: @message', [
      '@order_id' => $data['order_id'],
      '@message' => $message,
    ]);

    $this->apiWrapper->updateOrderStatus($data['tracking_id'], $this->knetSettings->get('payment_processed'), $message);

    // Delete the data from DB.
    \Drupal::state()->delete($state_key);

    $response = new RedirectResponse(Url::fromRoute('acq_checkout.form', ['step' => 'confirmation'])->toString());
    $response->send();
  }

  /**
   * Page callback for error state.
   */
  public function error($order_id) {
    $order = _alshaya_acm_checkout_get_last_order_from_session();

    if ($order['order_id'] != $order_id) {
      return new AccessDeniedHttpException();
    }

    $message = $this->t('Something went wrong, we dont have proper error message.');

    $message .= PHP_EOL . $this->t('Debug info:') . PHP_EOL;
    foreach ($_GET as $key => $value) {
      $message .= $key . ': ' . $value . PHP_EOL;
    }

    $this->logger->error('KNET payment failed for @order_id: @message', [
      '@order_id' => $order_id,
      '@message' => $message,
    ]);

    $this->apiWrapper->updateOrderStatus($order_id, $this->knetSettings->get('payment_failed'), $message);

    $response = new RedirectResponse(Url::fromRoute('acq_checkout.form', ['step' => 'confirmation'])->toString());
    $response->send();
  }

  /**
   * Page callback for internal error state.
   */
  public function internalError($state_key) {
    $data = \Drupal::state()->get($state_key);

    if (empty($data)) {
      return new AccessDeniedHttpException();
    }

    $message = '';

    foreach ($data as $key => $value) {
      $message .= $key . ': ' . $value . PHP_EOL;
    }

    $this->logger->error('KNET payment failed for @order_id: @message', [
      '@order_id' => $data['order_id'],
      '@message' => $message,
    ]);

    $this->apiWrapper->updateOrderStatus($data['tracking_id'], $this->knetSettings->get('payment_failed'), $message);

    // Delete the data from DB.
    \Drupal::state()->delete($state_key);

    $response = new RedirectResponse(Url::fromRoute('acq_checkout.form', ['step' => 'confirmation'])->toString());
    $response->send();
  }

}
