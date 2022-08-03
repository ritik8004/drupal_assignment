<?php

namespace Drupal\alshaya_acm_checkout\EventSubscriber;

use Drupal\acq_commerce\Response\NeedsRedirectException;
use Drupal\Core\EventSubscriber\HttpExceptionSubscriberBase;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Log a 403 redirect for checkout pages.
 */
class CheckoutAccessDeniedLoggerSubscriber extends HttpExceptionSubscriberBase {

  use LoggerChannelTrait;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a new AccessDeniedLoggerSubscriber.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  protected function getHandledFormats() {
    return ['html'];
  }

  /**
   * {@inheritdoc}
   */
  protected static function getPriority() {
    return 999;
  }

  /**
   * Logs details around 403 on checkout pages.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The Event to process.
   */
  public function on403(GetResponseEvent $event) {
    if ($this->routeMatch->getRouteName() === 'acq_checkout.form') {
      $this->logDetails($event->getException(), '403');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onException(GetResponseForExceptionEvent $event) {
    $exception = $event->getException();

    if ($exception instanceof NeedsRedirectException) {
      $this->logDetails($exception, '302');
    }

    parent::onException($event);
  }

  /**
   * Log details for exception.
   *
   * @param \Exception $exception
   *   Exception.
   * @param string $error
   *   Error identifier.
   */
  protected function logDetails(\Exception $exception, string $error) {
    try {
      $this->getLogger('CheckoutLogger')->warning('@error error was thrown from line @line on file @file', [
        '@error' => $error,
        '@line' => $exception->getLine(),
        '@file' => $exception->getFile(),
      ]);
    }
    catch (\Throwable $e) {
      $this->getLogger('CheckoutLogger')->warning('@error error was thrown and failed to get line and file for type: @type. Message: @message', [
        '@error' => $error,
        '@type' => $exception::class,
        '@message' => $e->getMessage(),
      ]);
    }
  }

}
