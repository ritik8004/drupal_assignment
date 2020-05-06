<?php

namespace Drupal\alshaya_acm_checkout\EventSubscriber;

use Drupal\Core\EventSubscriber\HttpExceptionSubscriberBase;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Routing\RouteMatchInterface;
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
    return 10;
  }

  /**
   * Logs details around 403 on checkout pages.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The Event to process.
   */
  public function on403(GetResponseEvent $event) {
    if ($this->routeMatch->getRouteName() === 'acq_checkout.form') {
      $exception = $event->getException();

      try {
        $this->getLogger()->warning('403 error was thrown from line @line on file @file', [
          '@line' => $exception->getLine(),
          '@file' => $exception->getFile(),
        ]);
      }
      catch (\Throwable $e) {
        $this->getLogger()->warning('403 error was thrown and failed to get line and file for type: @type', [
          '@type' => get_class($exception),
        ]);
      }
    }
  }

}
