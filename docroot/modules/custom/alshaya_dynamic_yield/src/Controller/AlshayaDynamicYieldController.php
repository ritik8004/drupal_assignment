<?php

namespace Drupal\alshaya_dynamic_yield\Controller;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Returns responses for Alshaya Dynamic Yield routes.
 */
class AlshayaDynamicYieldController extends ControllerBase {

  /**
   * The date time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $dateTime;

  /**
   * The AlshayaDynamicYieldController constructor.
   *
   * @param \Drupal\Component\Datetime\TimeInterface $date_time
   *   The datatime object.
   */
  public function __construct(TimeInterface $date_time) {
    $this->dateTime = $date_time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('datetime.time'),
    );
  }

  /**
   * Sets cookie for DY Intelligent tracking protocol.
   *
   * @see https://support.dynamicyield.com/hc/en-us/articles/360007211797-Safari-s-Intelligent-Tracking-Prevention-ITP-Policy-and-DYID-Retention#code-examples-0-4
   */
  public function intelligentTrackingProtocol(Request $request) {
    $dyid_cookie = $request->cookies->get('_dyid');

    $response = new Response();

    // Double check that _dyid_server cookie does not exist.
    if (!$request->cookies->get('_dyid_server')) {
      // Add _dyid_server cookie.
      $response->headers->setCookie(new Cookie(
        '_dyid_server',
        $dyid_cookie,
        $this->dateTime->getRequestTime() + 31540000000000,
        '/',
        NULL,
        NULL,
        FALSE
      ));
    }

    return $response;
  }

}
