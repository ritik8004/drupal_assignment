<?php

namespace Drupal\alshaya_knet\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\State\StateInterface;
use Drupal\alshaya_knet\Helper\KnetHelper;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class KnetController.
 */
class KnetController extends ControllerBase {

  /**
   * K-Net helper.
   *
   * @var \Drupal\alshaya_knet\Helper\KnetHelper
   */
  protected $knetHelper;

  /**
   * State object.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * KnetController constructor.
   *
   * @param \Drupal\alshaya_knet\Helper\KnetHelper $knet_helper
   *   Knet helper.
   * @param \Drupal\Core\State\StateInterface $state
   *   State object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory.
   */
  public function __construct(KnetHelper $knet_helper,
                              StateInterface $state,
                              LoggerChannelFactoryInterface $logger_factory) {

    $this->knetHelper = $knet_helper;
    $this->state = $state;
    $this->logger = $logger_factory->get('alshaya_knet');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_knet.helper'),
      $container->get('state'),
      $container->get('logger.factory')
    );
  }

  /**
   * Page callback to process the payment and return redirect URL.
   */
  public function response() {
    $quote_id = isset($_POST['udf3']) ? $_POST['udf3'] : '';
    try {
      if (empty($quote_id)) {
        throw new \Exception();
      }
    }
    catch (\Exception $e) {
      $this->logger->error('Invalid KNET response call found.<br>POST: @message', [
        '@message' => json_encode($_POST),
      ]);
      throw new NotFoundHttpException();
    }

    $response['payment_id'] = $_POST['paymentid'];
    $response['result'] = $_POST['result'];
    $response['post_date'] = $_POST['postdate'];
    $response['transaction_id'] = $_POST['tranid'];
    $response['auth_code'] = $_POST['auth'];
    $response['ref'] = $_POST['ref'];
    $response['tracking_id'] = $_POST['trackid'];
    $response['user_id'] = $_POST['udf1'];
    $response['customer_id'] = $_POST['udf2'];
    $response['quote_id'] = $_POST['udf3'];
    $response['state_key'] = $_POST['udf4'];

    $this->knetHelper->processKnetResponse($response);

  }

  /**
   * Page callback for success state.
   */
  public function success($state_key) {
    $data = $this->state->get($state_key);

    if (empty($data)) {
      $this->logger->warning('KNET success page requested with invalid state_key: @state_key', [
        '@state_key' => $state_key,
      ]);

      throw new AccessDeniedHttpException();
    }

    return $this->knetHelper->processKnetSuccess($state_key, $data);
  }

  /**
   * Page callback for error state.
   */
  public function error($quote_id) {
    return $this->knetHelper->processKnetError($quote_id);
  }

  /**
   * Page callback for failed state.
   */
  public function failed($state_key) {
    return $this->knetHelper->processKnetFailed($state_key);
  }

}
