<?php

namespace Drupal\alshaya_knet\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\alshaya_knet\Helper\KnetHelper;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\TempStore\SharedTempStoreFactory;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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
   * Stores the tempstore factory.
   *
   * @var \Drupal\Core\TempStore\SharedTempStore
   */
  protected $tempStore;

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
   * @param \Drupal\Core\TempStore\SharedTempStoreFactory $temp_store_factory
   *   The factory for the temp store object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory.
   */
  public function __construct(
    KnetHelper $knet_helper,
    SharedTempStoreFactory $temp_store_factory,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->knetHelper = $knet_helper;
    $this->tempStore = $temp_store_factory->get('knet');
    $this->logger = $logger_factory->get('alshaya_knet');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_knet.helper'),
      $container->get('tempstore.shared'),
      $container->get('logger.factory')
    );
  }

  /**
   * Page callback to process the payment and return redirect URL.
   */
  public function response() {
    $data = $_POST;

    // For new K-Net toolkit, parse and decrypt the response first.
    if (!empty($data) && $this->knetHelper->useNewKnetToolKit()) {
      try {
        $data = $this->knetHelper->parseAndPrepareKnetData($data);
      }
      catch (\Exception $e) {
        $this->logger->error('K-Net is not configured properly<br>POST: @message', [
          '@message' => json_encode($data),
        ]);
        throw new AccessDeniedHttpException();
      }
    }

    $quote_id = isset($data['udf3']) ? $data['udf3'] : '';
    try {
      if (empty($quote_id)) {
        throw new \Exception();
      }
    }
    catch (\Exception $e) {
      $this->logger->error('Invalid KNET response call found.<br>POST: @message', [
        '@message' => json_encode($data),
      ]);
      throw new AccessDeniedHttpException();
    }

    $response['payment_id'] = $data['paymentid'];
    $response['result'] = $data['result'];
    $response['post_date'] = $data['postdate'];
    $response['transaction_id'] = $data['tranid'];
    $response['auth_code'] = $data['auth'];
    $response['ref'] = $data['ref'];
    $response['tracking_id'] = $data['trackid'];
    $response['user_id'] = $data['udf1'];
    $response['customer_id'] = $data['udf2'];
    $response['quote_id'] = $data['udf3'];
    $response['state_key'] = $data['udf4'];

    // For the new toolkit, payment id not available before redirecting to
    // PG. So adding the payment id in state variable later.
    if ($this->knetHelper->useNewKnetToolKit()
      && !empty($state = $this->tempStore->get($response['state_key']))) {
      $state['payment_id'] = $response['payment_id'];
      $this->tempStore->set($response['state_key'], $state);
    }

    return $this->knetHelper->processKnetResponse($response);

  }

  /**
   * Page callback for success state.
   */
  public function success($state_key) {
    $data = $this->tempStore->get($state_key);

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
