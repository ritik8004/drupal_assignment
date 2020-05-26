<?php

namespace Drupal\alshaya_facets_pretty_paths\EventSubscriber;

use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AlshayaFacetsPrettyPathsKernelEventsSubscriber.
 *
 * @package Drupal\alshaya_facets_pretty_paths\EventSubscriber
 */
class AlshayaFacetsPrettyPathsKernelEventsSubscriber implements EventSubscriberInterface {

  /**
   * Check environment specific config on each request.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event to process.
   */
  public function filterBadBotRequests(GetResponseEvent $event) {
    $request = $event->getRequest();
    $pretty_path = explode('/--', $request->getRequestUri())[1] ?? '';

    $count = 0;
    foreach (explode('--', $pretty_path) as $facet_filter) {
      $count += count(explode('-', $facet_filter)) - 1;
    }

    // Set noindex header if more than two filters.
    if ($pretty_path && $count > 2) {
      header('X-Robots-Tag', 'noindex');

      $user_agent = strtolower($event->getRequest()->headers->get('User-Agent'));
      if (Settings::get('block_bad_bots', FALSE) && strpos($user_agent, 'bot') !== FALSE) {
        $event->stopPropagation();

        $response = new Response();
        // Ensure these requests are not cached so when real user requests
        // it is still served fine.
        $response->setMaxAge(0);
        return $response;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['filterBadBotRequests', 999];
    return $events;
  }

}
