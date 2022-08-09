<?php

namespace Drupal\alshaya_facets_pretty_paths\EventSubscriber;

use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Alshaya Facets Pretty Paths Kernel EventsSubscriber.
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
      // Reduce one - facet alias is also added here.
      $count += count(explode('-', $facet_filter)) - 1;
    }

    // Set noindex header if more than two filters.
    if ($pretty_path && $count > Settings::get('nonindexable_plp_filter_count')) {
      header('X-Robots-Tag: noindex');

      if (Settings::get('serve_empty_response_for_nonindexable_plp_to_bots', FALSE)) {
        $user_agent = strtolower($event->getRequest()->headers->get('User-Agent'));
        $bad_bot_agents = Settings::get('bad_bot_user_agents', []);

        foreach ($bad_bot_agents as $bad_bot_agent) {
          // Add only "Googlebot" for instance to block all user agents with
          // this string.
          if (str_contains($user_agent, $bad_bot_agent)) {
            $event->stopPropagation();

            $response = new Response();
            // Ensure these requests are not cached so when real user requests
            // it is still served fine.
            $response->setMaxAge(0);
            return $response;
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[KernelEvents::REQUEST][] = ['filterBadBotRequests', 999];
    return $events;
  }

}
