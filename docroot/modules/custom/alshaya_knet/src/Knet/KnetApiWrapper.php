<?php

namespace Drupal\alshaya_knet\Knet;

use GuzzleHttp\Client;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

/**
 * Class Knet Api Wrapper.
 *
 * @package Drupal\alshaya_knet\Knet
 */
class KnetApiWrapper {

  public const ACTION_INQUIRE = 8;

  public const ENDPOINT_INQUIRE = '/kpg/tranPipe.htm';

  /**
   * KNET Url.
   *
   * @var string
   */
  protected $url;

  /**
   * KNET Tranportal ID.
   *
   * @var string
   */
  protected $tranportalId;

  /**
   * KNET Tranportal Password.
   *
   * @var string
   */
  protected $tranportalPassword;

  /**
   * KnetApiWrapper constructor.
   *
   * @param string $knet_url
   *   KNET URL (Test or Live).
   * @param string $tranportal_id
   *   KNET Tranportal ID.
   * @param string $tranportal_password
   *   KNET Tranportal Password.
   */
  public function __construct(string $knet_url,
                              string $tranportal_id,
                              string $tranportal_password) {
    $this->url = $knet_url;
    $this->tranportalId = $tranportal_id;
    $this->tranportalPassword = $tranportal_password;
  }

  /**
   * Get KNET Transaction info.
   *
   * @param string $tracking_id
   *   KNET Tracking ID.
   * @param string|float|int $amount
   *   Amount.
   *
   * @return array
   *   KNET Info for the Tracking ID.
   */
  public function getTransactionInfoByTrackingId(string $tracking_id, $amount) {
    $query = [
      'param' => 'tranInit',
    ];

    $body = [];
    $body['id'] = $this->tranportalId;
    $body['password'] = $this->tranportalPassword;
    $body['action'] = self::ACTION_INQUIRE;
    $body['amt'] = $amount;
    $body['udf5'] = 'TrackID';
    $body['transid'] = $tracking_id;
    $body['trackid'] = $tracking_id;

    $encoder = new XmlEncoder('body');
    $body = $encoder->encode($body, 'xml');

    $client = $this->createClient();

    $response = $client->post(self::ENDPOINT_INQUIRE, [
      'query' => $query,
      'headers' => [
        'Content-Type' => 'text/xml; charset=UTF8',
      ],
      'body' => $body,
    ]);

    if ($response->getStatusCode() !== 200) {
      throw new \Exception($response->getBody(), $response->getStatusCode());
    }

    $content = $response->getBody()->getContents();
    return $encoder->decode('<response>' . $content . '</response>', 'json');
  }

  /**
   * Crate a new client object.
   *
   * Create a Guzzle http client configured to connect to the
   * checkout.com instance.
   *
   * @return \GuzzleHttp\Client
   *   Object of initialized client.
   */
  protected function createClient() {
    $configuration = [
      'base_uri' => $this->url,
      'verify'   => TRUE,
    ];

    return (new Client($configuration));
  }

}
