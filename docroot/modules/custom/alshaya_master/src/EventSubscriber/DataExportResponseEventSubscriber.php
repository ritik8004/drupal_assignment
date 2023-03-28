<?php

namespace Drupal\alshaya_master\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Adds cache control headers to export data.
 *
 * We are providing different export data to MDC like product, category,
 * promotion and group by category data.
 * But as these csv files are created in the files folder, they are cached by
 * the browser, CDN and Varnish for a very long duration.
 * So we set cache headers here so that in case the origin file is modified,
 * we get the updated data in the download next time.
 */
class DataExportResponseEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::RESPONSE => [
        ['onResponse'],
      ],
    ];
  }

  /**
   * Adds cache control headers to the response.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $responseEvent
   *   Response event.
   */
  public function onResponse(ResponseEvent $responseEvent) {
    $request = $responseEvent->getRequest();
    if (!preg_match('/exports\/v2\//', $request->getPathInfo())) {
      return;
    }

    $response = $responseEvent->getResponse();
    $response->headers->set('Cache-Control', 'no-cache', 'must-revalidate');
    $responseEvent->setResponse($response);
  }

}
