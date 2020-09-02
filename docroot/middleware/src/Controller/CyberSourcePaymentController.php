<?php

namespace App\Controller;

use App\Service\Cybersource\CybersourceHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;

/**
 * Class CyberSourcePaymentController.
 */
class CyberSourcePaymentController {

  /**
   * Cybersource Helper.
   *
   * @var \App\Service\Cybersource\CybersourceHelper
   */
  protected $cybersourceHelper;

  /**
   * Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * CyberSourcePaymentController constructor.
   *
   * @param \App\Service\Cybersource\CybersourceHelper $cybersource_helper
   *   Cybersource Helper.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   */
  public function __construct(
    CybersourceHelper $cybersource_helper,
    LoggerInterface $logger
  ) {
    $this->cybersourceHelper = $cybersource_helper;
    $this->logger = $logger;
  }

  /**
   * Page callback to get cybersource token.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Response data in JSON.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getCybersourceToken() {
    $response = $this->cybersourceHelper->getToken();
    return new JsonResponse($response);
  }

  /**
   * Response callback for cybersource.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Script to trigger event in parent window.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function finaliseCybersource() {
    $response = $this->cybersourceHelper->finalise();

    // Code for CustomEvent here is added to support IE11.
    $script = '
      <script type="text/javascript">
        (function () {
          if ( typeof window.CustomEvent === "function" ) return false;
          function CustomEvent ( event, params ) {
            params = params || { bubbles: false, cancelable: false, detail: undefined };
            var evt = document.createEvent("CustomEvent");
            evt.initCustomEvent( event, params.bubbles, params.cancelable, params.detail );
            return evt;
          }
          CustomEvent.prototype = window.Event.prototype;
          window.CustomEvent = CustomEvent;
        })();
        
        var event = new CustomEvent("cybersourcePaymentUpdate", {bubbles: true, detail: ' . json_encode($response) . '});
        window.parent.document.dispatchEvent(event);
      </script>
    ';

    return new Response($script);
  }

}
