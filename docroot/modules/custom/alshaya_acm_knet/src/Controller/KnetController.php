<?php

namespace Drupal\alshaya_acm_knet\Controller;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\alshaya_acm_customer\OrdersManager;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
   * Alshaya API Wrapper object.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $alshayaApiWrapper;

  /**
   * Array containing knet settings from config.
   *
   * @var array
   */
  protected $knetSettings;

  /**
   * Orders Manager object.
   *
   * @var \Drupal\alshaya_acm_customer\OrdersManager
   */
  protected $ordersManager;

  /**
   * Constructor.
   *
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   API wrapper object.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $alshaya_api_wrapper
   *   Alshaya API wrapper object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\alshaya_acm_customer\OrdersManager $orders_manager
   *   Orders Manager object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Factory object.
   */
  public function __construct(APIWrapper $api_wrapper, AlshayaApiWrapper $alshaya_api_wrapper, ConfigFactoryInterface $config_factory, OrdersManager $orders_manager, LoggerChannelFactoryInterface $logger_factory) {
    $this->apiWrapper = $api_wrapper;
    $this->alshayaApiWrapper = $alshaya_api_wrapper;
    $this->knetSettings = $config_factory->get('alshaya_acm_knet.settings');
    $this->ordersManager = $orders_manager;
    $this->logger = $logger_factory->get('alshaya_acm_knet');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acq_commerce.api'),
      $container->get('alshaya_api.api'),
      $container->get('config.factory'),
      $container->get('alshaya_acm_customer.orders_manager'),
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
      throw new AccessDeniedHttpException();
    }

    $message = '';

    foreach ($data as $key => $value) {
      $message .= $key . ': ' . $value . PHP_EOL;
    }

    $this->logger->info('KNET payment complete for @order_id: @message', [
      '@order_id' => $data['order_id'],
      '@message' => $message,
    ]);

    // Direct Magento API call to create transaction entry, we don't have it in
    // conductor yet.
    $this->alshayaApiWrapper->addKnetTransaction($data['order_id'], $data['transaction_id']);

    // @TODO: Below API call is still kept here as status is not updated in
    // previous call still.
    $this->apiWrapper->updateOrderStatus($data['tracking_id'], $this->knetSettings->get('payment_processed'), $message);

    // Delete the data from DB.
    \Drupal::state()->delete($state_key);

    $this->ordersManager->clearOrderCache($data['email'], $data['user_id']);

    return $this->redirect('acq_checkout.form', ['step' => 'confirmation']);
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

    $this->ordersManager->clearOrderCache(\Drupal::currentUser()->getEmail(), \Drupal::currentUser()->id());

    return $this->redirect('acq_checkout.form', ['step' => 'confirmation']);
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

    return $this->redirect('acq_checkout.form', ['step' => 'confirmation']);
  }

}
