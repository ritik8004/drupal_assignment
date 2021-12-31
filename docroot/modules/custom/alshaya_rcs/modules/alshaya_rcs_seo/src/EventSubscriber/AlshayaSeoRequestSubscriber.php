<?php

namespace Drupal\alshaya_rcs_seo\EventSubscriber;

use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

/**
 * Perform redirection on PLP if trailing slash not present.
 */
class AlshayaSeoRequestSubscriber implements EventSubscriberInterface {

  /**
   * Page cache kill service.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $killSwitch;

  /**
   * A router implementation which does not check access.
   *
   * @var \Symfony\Component\Routing\Matcher\UrlMatcherInterface
   */
  protected $accessUnawareRouter;

  /**
   * Constructs a AlshayaSeoRequestSubscriber object.
   *
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $kill_switch
   *   Page cache kill service.
   * @param \Symfony\Component\Routing\Matcher\UrlMatcherInterface $accessUnawareRouter
   *   A router implementation which does not check access.
   */
  public function __construct(KillSwitch $kill_switch, UrlMatcherInterface $accessUnawareRouter) {
    $this->killSwitch = $kill_switch;
    $this->accessUnawareRouter = $accessUnawareRouter;
  }

  /**
   * Performs a redirect if trailing slash not present in PLP.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The Event to process.
   */
  public function onKernelRequestRedirect(GetResponseEvent $event) {
    $request = $event->getRequest();
    $request_path = $request->getPathInfo();

    $result = $this->accessUnawareRouter->match($request_path);
    if ($result['_route'] && $result['_route'] == 'entity.taxonomy_term.canonical' || $result['_route'] == 'alshaya_master.home') {
      if (substr($request_path, -1) != '/') {
        $request_uri = $request_path . '/';
        $response = new RedirectResponse($request_uri, 302);
        $response->headers->set('cache-control', 'must-revalidate, no-cache, no-store, private');
        $event->setResponse($response);

        // Disable page cache, we want to change the redirect based on cookie.
        $this->killSwitch->trigger();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onKernelRequestRedirect', 100];
    return $events;
  }

}
