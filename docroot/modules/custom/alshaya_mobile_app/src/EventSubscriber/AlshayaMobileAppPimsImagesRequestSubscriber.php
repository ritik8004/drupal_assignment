<?php

namespace Drupal\alshaya_mobile_app\EventSubscriber;

use Drupal\alshaya_acm_product\Event\ProductUpdatedEvent;
use Drupal\alshaya_acm_product\SkuImagesHelper;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Redirects requests from mobile APP for PIMS images.
 */
class AlshayaMobileAppPimsImagesRequestSubscriber implements EventSubscriberInterface {

  /**
   * PIMS styles mapping table name constant.
   */
  const PIMS_IMAGE_STYLES_MAPPING_TABLE = 'pims_style_mapping';

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
   * SKU Images Manager service.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $skuImagesManager;

  /**
   * SKU Images Helper.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesHelper
   */
  protected $skuImagesHelper;

  /**
   * The class constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   * @param \Drupal\Core\Database\Connection $database
   *   Database service.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU Images Manager service.
   * @param \Drupal\alshaya_acm_product\SkuImagesHelper $sku_images_helper
   *   SKU Images Helper service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    Connection $database,
    SkuImagesManager $sku_images_manager,
    SkuImagesHelper $sku_images_helper
  ) {
    $this->configFactory = $config_factory;
    $this->database = $database;
    $this->skuImagesManager = $sku_images_manager;
    $this->skuImagesHelper = $sku_images_helper;
  }

  /**
   * Prepares the PIMS image URL.
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
    preg_match('/styles\/(\w+)\/public\/(.*?)$/', $path, $matches);
    // Image style is mandatory. If not present, return 400 error.
    if (empty($matches[1])) {
      $event->stopPropagation();
      $response = new Response('Image style is missing in the url.', Response::HTTP_BAD_REQUEST);
      $event->setResponse($response);
      return;
    }

    // Fetch the styled PIMS image URL from the database.
    $query = $this->database->select(self::PIMS_IMAGE_STYLES_MAPPING_TABLE, 'mapping');
    $query->addField('mapping', 'styled_url');
    $query->condition('original_url', $matches[2]);
    $query->condition('style', $matches[1]);
    $pims_styled_image_url = $query->execute()->fetchField();
    if (empty($pims_styled_image_url)) {
      $event->stopPropagation();
      $response = new Response('Styled image not found.', Response::HTTP_NOT_FOUND);
      $event->setResponse($response);
      return;
    }

    // Redirect the request to the URL of the styled image.
    // We use TrustedRedirectResponse here in order to allow redirecting to
    // external domain. For eg. on PPROD, we will have PROD image urls, so
    // using normal RedirectResponse() will not allow us to do that redirect.
    $response = new TrustedRedirectResponse($pims_styled_image_url);
    // Set max-age for 30 days.
    $response->setMaxAge(2592000);
    $response->setExpires(new \DateTime('+1 month'));
    $event->stopPropagation();
    $event->setResponse($response);
  }

  /**
   * Adds/updates entry in pims_style_mapping table.
   *
   * @param \Drupal\alshaya_acm_product\Event\ProductUpdatedEvent $event
   *   Event object.
   */
  public function onProductUpdated(ProductUpdatedEvent $event) {
    if ($event->getOperation() === ProductUpdatedEvent::EVENT_DELETE) {
      return;
    }

    // Prepare the mapping data that needs to be added to the table.
    $sku_entity = $event->getSku();
    $media = $this->skuImagesManager->getProductMedia($sku_entity, 'pdp');
    $data = [];
    foreach ($media['media_items']['images'] as $media_item) {
      $styled_images = $this->skuImagesHelper->getAllStyledImages($media_item);
      // Remove the domain from the base image URL.
      // $base_image_path will be like 'assets/HNM/13601843/24c83ed80665...'.
      preg_match('/assets\/(.*?)$/', $media_item['drupal_uri'], $base_image_path);
      $base_image_path = $base_image_path[0];
      // Prepare the data.
      foreach ($styled_images as $image_style => $styled_image_url) {
        $data[] = [
          'original_url' => $base_image_path,
          'style' => $image_style,
          'styled_url' => $styled_image_url,
        ];
      }
    }

    // Enter the data into the table.
    $query = $this->database->upsert(self::PIMS_IMAGE_STYLES_MAPPING_TABLE);
    $query->fields(['original_url', 'style', 'styled_url']);
    foreach ($data as $value) {
      $query->values($value);
    }
    $query->key('original_url');
    $query->execute();
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onKernelRequestPimsImage', 255];
    $events[ProductUpdatedEvent::EVENT_NAME][] = ['onProductUpdated', 100];
    return $events;
  }

}
