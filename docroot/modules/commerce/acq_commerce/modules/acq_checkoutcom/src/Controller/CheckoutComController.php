<?php

namespace Drupal\acq_checkoutcom\Controller;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\acq_commerce\Connector\ConnectorException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the checkout form page.
 */
class CheckoutComController implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * ACM API Version.
   *
   * @var string
   */
  protected $apiVersion;

  /**
   * APIWrapper service object.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  protected $apiWrapper;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * CheckoutComController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   ConfigFactoryInterface object.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   APIWrapper service object.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger channel.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    APIWrapper $api_wrapper,
    LoggerInterface $logger
  ) {
    $this->apiVersion = $config_factory->get('acq_commerce.conductor')->get('api_version');
    $this->apiWrapper = $api_wrapper;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('acq_commerce.agent_api'),
      $container->get('logger.factory')->get('acq_checkoutcom')
    );
  }

  /**
   * Page callback to process checkoutcom response.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   */
  public function status(Request $request) {
    $post_data = $request->query->get('cko-payment-token');
    // Todo: Replace with magento api call.
    $url = "https://sandbox.checkout.com/api2/v2/charges/$post_data";
    $header = [
      'Content-Type: application/json;charset=UTF-8',
      'Authorization: sk_test_863d1545-5253-4387-b86b-df6a86797baa',
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    $output = curl_exec($ch);

    curl_close($ch);
    $decoded = json_decode($output, TRUE);
    echo '<pre>';
    print_r($decoded);
    echo '</pre>';
    die();
  }

  /**
   * Gets the token from Magento.
   *
   * @return mixed
   *   API response containing all the data to be passed on to Cybersource.
   *
   * @throws \Exception
   *   Failed request exception.
   */
  public function getSubscriptionRequest() {
    $endpoint = $this->apiVersion . '/agent/token/checkoutcom';

    $doReq = function ($client, $opt) use ($endpoint) {
      return ($client->get($endpoint, $opt));
    };

    try {
      return $this->apiWrapper->tryAgentRequest($doReq, 'getSubscriptionRequest');
    }
    catch (ConnectorException $e) {
      $this->logger->warning('Error occurred while getting cybersource token for cart id: %cart_id and card type: %card_type: %message', [
        '%message' => $e->getMessage(),
      ]);

      throw new \Exception($e->getMessage(), $e->getCode());
    }
  }

}
