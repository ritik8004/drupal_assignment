<?php

namespace Drupal\alshaya_mobile_app\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Redirects requests from mobile APP for PIMS images.
 */
class AlshayaMobileAppPimsImagesRequestSubscriber implements EventSubscriberInterface {

  /**
   * The config object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The class constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   * @param \Drupal\Core\Database\Connection $database
   *   Database service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    Connection $database
  ) {
    $this->configFactory = $config_factory;
    $this->database = $database;
  }

  /**
   * Initializes the language manager at the beginning of the request.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The Event to process.
   */
  public function onKernelRequestPimsImage(GetResponseEvent $event) {
    if (!($event->isMasterRequest())) {
      return;
    }
    // Do not proceed if the request is not for PIMS assets made by mobile app.
    $path = $event->getRequest()->getRequestUri();
    $matches = [];
    if (strpos($path, 'pims-assets') === FALSE) {
      return;
    }

    // Do not proceed if we do not need to process the image urls.
    $config = $this->configFactory->get('alshaya_mobile_app.settings');
    if (!($config->get('process_image_url_for_pims'))) {
      return;
    }

    // Fetch the image style from the path.
    $matches = [];
    preg_match('/styles\/(\w+)\/public/', $path, $matches);
    // Image style is mandatory. If not present, return 400 error.
    if (empty($matches[1])) {
      $event->stopPropagation();
      $response = new Response('Image style is missing in the url.', Response::HTTP_BAD_REQUEST);
      $event->setResponse($response);
      return;
    }

    // Fetch the styled PIMS image URL from the database.
    $image_style = $matches[1];
    $query = $this->database->select('pims_style_mapping');
    $query->addField('pims_style_mapping', 'styled_url');
    $query->condition('original_url', $path);
    $query->condition('style', $image_style);
    $pims_styled_image_url = $query->execute()->fetchField();
    if (empty($pims_styled_image_url)) {
      $event->stopPropagation();
      $response = new Response('Styled image not found.', Response::HTTP_NOT_FOUND);
      $event->setResponse($response);
      return;
    }

    // Redirect the request to the URL of the styled image.
    $response = new RedirectResponse($pims_styled_image_url);
    // Set max-age for 30 days.
    $response->setMaxAge(2592000);
    $response->setExpires(new \DateTime('+1 month'));
    $event->stopPropagation();
    $event->setResponse($response);
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onKernelRequestPimsImage', 255];
    return $events;
  }

}
