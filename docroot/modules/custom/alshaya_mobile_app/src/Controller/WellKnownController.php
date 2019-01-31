<?php

namespace Drupal\alshaya_mobile_app\Controller;

use Drupal\alshaya_mobile_app\Form\AndroidConfigForm;
use Drupal\alshaya_mobile_app\Form\IosConfigForm;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Customer controller to .well-known links.
 */
class WellKnownController extends ControllerBase {

  /**
   * Page callback for .well-known/assetlinks.json.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *   JSON Response.
   */
  public function assetLinks() {
    $config = $this->config(AndroidConfigForm::CONFIG_NAME);

    $package_name = $config->get('package_name');
    if (empty($package_name)) {
      throw new NotFoundHttpException();
    }

    $body = [
      'relation' => [
        'delegate_permission/common.handle_all_urls',
      ],
      'target' => [
        'namespace' => 'android_app',
        'package_name' => $package_name,
        'sha256_cert_fingerprints' => explode(PHP_EOL, $config->get('sha256_cert_fingerprints')),
      ],
    ];

    $response = new CacheableJsonResponse($body);

    // Handle caching.
    $cacheMeta = new CacheableMetadata();
    $cacheMeta->addCacheTags($config->getCacheTags());
    $response->addCacheableDependency($cacheMeta);

    return $response;
  }

  /**
   * Page callback for .well-known/apple-app-site-association.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *   JSON Response.
   */
  public function appleAppSiteAssociation() {
    $config = $this->config(IosConfigForm::CONFIG_NAME);

    $appID = $config->get('appID');
    if (empty($appID)) {
      throw new NotFoundHttpException();
    }

    $body = [
      'applinks' => [
        'apps' => [],
        'details' => [
          'appID' => $appID,
          'paths' => explode(PHP_EOL, $config->get('paths')),
        ],
      ],
    ];

    $response = new CacheableJsonResponse($body);

    // Handle caching.
    $cacheMeta = new CacheableMetadata();
    $cacheMeta->addCacheTags($config->getCacheTags());
    $response->addCacheableDependency($cacheMeta);

    return $response;
  }

}
