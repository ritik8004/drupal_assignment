<?php

namespace Drupal\alshaya_acm_knet\Controller;

use Drupal\acq_commerce\Conductor\APIWrapper;
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
   * Constructor.
   *
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   APIWrapper object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Factory object.
   */
  public function __construct(APIWrapper $api_wrapper, LoggerChannelFactoryInterface $logger_factory) {
    $this->apiWrapper = $api_wrapper;
    $this->logger = $logger_factory->get('alshaya_acm_knet');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acq_commerce.api'),
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
    $response['internal_order_id'] = $_POST['udf4'];

    // @TODO: Should we think about encrypting this message?
    $result_params = '?response=' . base64_encode(json_encode($response));

    if ($response['result'] == 'CAPTURED') {
      $result_url = Url::fromRoute('alshaya_acm_knet.success', ['order_id' => $response['order_id']], ['absolute' => TRUE])->toString();
      $this->logger->info('KNET update for @order_id: @message', ['@order_id' => $response['order_id'], '@message' => json_encode($response)]);
    }
    else {
      $result_url = Url::fromRoute('alshaya_acm_knet.error', ['order_id' => $response['order_id']], ['absolute' => TRUE])->toString();
      $this->logger->error('KNET update for @order_id: @message', ['@order_id' => $response['order_id'], '@message' => json_encode($response)]);
    }

    print 'REDIRECT=' . $result_url . $result_params;
    exit;
  }

  /**
   * Page callback for success state.
   */
  public function success($order_id) {
    $data = json_decode(base64_decode(\Drupal::request()->get('response')), TRUE);

    if (empty($data) || $data['order_id'] != $order_id) {
      return new AccessDeniedHttpException();
    }

    $message = '';

    foreach ($data as $key => $value) {
      $message .= $key . ': ' . $value . PHP_EOL;
    }

    $this->apiWrapper->updateOrderStatus($data['internal_order_id'], 'pending', $message);

    $response = new RedirectResponse(Url::fromRoute('acq_checkout.form', ['step' => 'confirmation']));
    $response->send();
  }

  /**
   * Page callback for error state.
   */
  public function error($order_id) {
    $data = json_decode(base64_decode(\Drupal::request()->get('response')), TRUE);

    $message = $this->t('Something went wrong, we dont have proper error message.');

    // Error from our side, K-Net error won't have this params.
    if (empty($data) || $data['order_id'] != $order_id) {
      $message = '';

      foreach ($data as $key => $value) {
        $message .= $key . ': ' . $value . PHP_EOL;
      }
    }

    $this->apiWrapper->updateOrderStatus($data['internal_order_id'], 'payment_failed', $message);

    $response = new RedirectResponse(Url::fromRoute('acq_checkout.form', ['step' => 'confirmation']));
    $response->send();
  }

}
