<?php

namespace Drupal\alshaya_acm\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Alshaya Checkout Subscriber.
 *
 * Redirect users from cart/checkout to specific page if checkout is disabled.
 */
class AlshayaCheckoutSubscriber implements EventSubscriberInterface {

  use LoggerChannelTrait;

  /**
   * Route matcher.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * AlshayaCheckoutSubscriber constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route matcher.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   */
  public function __construct(RouteMatchInterface $route_match,
                              ConfigFactoryInterface $config_factory) {
    $this->routeMatch = $route_match;
    $this->configFactory = $config_factory;
  }

  /**
   * Redirect users from cart and checkout to user defined page.
   *
   * This is done only if the status is set to disable checkout.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event to process.
   */
  public function handleCheckoutStatusRedirects(GetResponseEvent $event) {
    $checkoutRoutes = [
      'acq_cart.cart',
      'acq_checkout.form',
      'alshaya_spc.checkout',
      'alshaya_spc.checkout.login',
    ];

    if (in_array($this->routeMatch->getRouteName(), $checkoutRoutes)) {
      // First get the status.
      $config = $this->configFactory->get('alshaya_acm.cart_config');
      $featureStatus = $config->get('checkout_feature') ?? 'enabled';

      if ($featureStatus === 'disabled') {
        try {
          $path = $config->get('checkout_disabled_page') ?? '/';
          $path = $path ?: '/';
          $url = Url::fromUserInput($path);
          $redirect = $url->toString();
        }
        catch (\Exception $e) {
          $this->getLogger('AlshayaCheckoutSubscriber')->warning(
            'Failed to load checkout disabled status page url: @path. Message; @message',
            [
              '@path' => $path,
              '@message' => $e->getMessage(),
            ]
          );

          $redirect = Url::fromUserInput('/')->toString();
        }
        $response = new TrustedRedirectResponse($redirect);
        $response->addCacheableDependency($config);
        $event->setResponse($response);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[KernelEvents::REQUEST][] = ['handleCheckoutStatusRedirects'];
    return $events;
  }

}
