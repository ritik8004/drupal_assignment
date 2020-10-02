<?php

namespace Drupal\alshaya_brand\StreamWrapper;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\DrupalKernel;
use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\StreamWrapper\LocalStream;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Creates a brand:// stream wrapper.
 */
class BrandFilesStreamWrapper extends LocalStream {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->t('Brand files');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Public local files common to all markets of a brand.');
  }

  /**
   * {@inheritdoc}
   */
  public function getDirectoryPath() {
    return static::basePath();
  }

  /**
   * {@inheritdoc}
   */
  public function getExternalUrl() {
    $path = str_replace('\\', '/', $this->getTarget());
    return static::baseUrl() . '/' . UrlHelper::encodePath($path);
  }

  /**
   * Finds and returns the base URL for brand://.
   *
   * Defaults to the current site's base URL plus directory path.
   *
   * Note that this static method is used by \Drupal\system\Form\FileSystemForm
   * so you should alter that form or substitute a different form if you change
   * the class providing the stream_wrapper.brand service.
   *
   * @return string
   *   The external base URL for brand://
   */
  public static function baseUrl() {
    $settings_base_url = Settings::get('file_brand_base_url', '');

    return $settings_base_url
      ? (string) $settings_base_url
      : $GLOBALS['base_url'] . '/' . static::basePath();
  }

  /**
   * Returns the base path for brand://.
   *
   * If we have a setting for the brand:// scheme's path, we use that.
   * Otherwise we build a reasonable default based on the site.path service if
   * it's available, or a default behavior based on the request.
   *
   * Note that this static method is used by \Drupal\system\Form\FileSystemForm
   * so you should alter that form or substitute a different form if you change
   * the class providing the stream_wrapper.public service.
   *
   * The site path is injectable from the site.path service:
   * @code
   * $base_path = PublicStream::basePath(\Drupal::service('site.path'));
   * @endcode
   *
   * @param \SplString $site_path
   *   (optional) The site.path service parameter, which is typically the path
   *   to sites/ in a Drupal installation. This allows you to inject the site
   *   path using services from the caller. If omitted, this method will use the
   *   global service container or the kernel's default behavior to determine
   *   the site path.
   *
   * @return string
   *   The base path for public:// typically sites/default/files.
   */
  public static function basePath(\SplString $site_path = NULL) {
    if ($site_path === NULL) {
      // Find the site path. Kernel service is not always available at this
      // point, but is preferred, when available.
      if (\Drupal::hasService('kernel')) {
        $site_path = \Drupal::service('site.path');
      }
      else {
        // If there is no kernel available yet, we call the static
        // findSitePath().
        $site_path = DrupalKernel::findSitePath(Request::createFromGlobals());
      }
    }
    return Settings::get('alshaya_brand_shared_folder', $site_path . '/files');
  }

}
