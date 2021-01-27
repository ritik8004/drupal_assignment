<?php

namespace Drupal\alshaya_brand\EventSubscriber;

use Drupal\alshaya_brand\StreamWrapper\BrandFilesStreamWrapper;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\Core\Url;
use Drupal\stage_file_proxy\FetchManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event subscriber to support StageFileProxy for brand files.
 *
 * @package Drupal\alshaya_brand\EventSubscriber
 */
class StageFileProxyProxySubscriber implements EventSubscriberInterface {

  use LoggerChannelTrait;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * The manager used to fetch the file against.
   *
   * @var \Drupal\stage_file_proxy\FetchManagerInterface
   */
  protected $manager;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Construct the FetchManager.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   * @param \GuzzleHttp\Client $client
   *   The HTTP client.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              RequestStack $request_stack,
                              FileSystemInterface $file_system,
                              Client $client) {
    $this->configFactory = $config_factory;
    $this->requestStack = $request_stack;
    $this->fileSystem = $file_system;
    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Priority 241 is just before stage_file_proxy.
    $events[KernelEvents::REQUEST][] = ['checkFileOrigin', 241];
    return $events;
  }

  /**
   * Set the optional service for stage file proxy.
   *
   * @param \Drupal\stage_file_proxy\FetchManagerInterface $manager
   *   Fetch Manager.
   */
  public function setStageFileProxy(FetchManagerInterface $manager) {
    $this->manager = $manager;
  }

  /**
   * Fetch the file from it's origin.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event to process.
   */
  public function checkFileOrigin(GetResponseEvent $event) {
    // Do nothing for sites where stage file proxy is disabled.
    if (empty($this->manager)) {
      return;
    }

    $config = $this->configFactory->get('stage_file_proxy.settings');

    // Get the origin server.
    $server = $config->get('origin');

    // Quit if no origin given.
    if (!$server) {
      return;
    }

    $file_dir = BrandFilesStreamWrapper::basePath();
    $request_path = $event->getRequest()->getPathInfo();

    $request_path = mb_substr($request_path, 1);

    if (strpos($request_path, '' . $file_dir) !== 0) {
      return;
    }

    $request_path = rawurldecode($request_path);
    // Path relative to file directory. Used for hotlinking.
    $relative_path = mb_substr($request_path, mb_strlen($file_dir) + 1);
    // If file is fetched and use_imagecache_root is set, original is used.
    $fetch_path = $relative_path;

    // Is this imagecache? Request the root file and let imagecache resize.
    // We check this first so locally added files have precedence.
    $original_path = $this->manager->styleOriginalPath($relative_path, TRUE);
    if ($original_path) {
      if (file_exists($original_path)) {
        // Imagecache can generate it without our help.
        return;
      }
      if ($config->get('use_imagecache_root')) {
        // Config says: Fetch the original.
        $fetch_path = StreamWrapperManager::getTarget($original_path);
      }
    }

    $query = $this->requestStack->getCurrentRequest()->query->all();
    $query_parameters = UrlHelper::filterQueryParameters($query);
    $options = [
      'verify' => $config->get('verify'),
    ];

    $remote_file_dir = $file_dir;

    if ($config->get('hotlink')) {

      $location = Url::fromUri("$server/$remote_file_dir/$relative_path", [
        'query' => $query_parameters,
        'absolute' => TRUE,
      ])->toString();

    }
    elseif ($this->fetch($server, $remote_file_dir, $fetch_path, $options)) {
      // Refresh this request & let the web server work out mime type, etc.
      $location = Url::fromUri('base://' . $request_path, [
        'query' => $query_parameters,
        'absolute' => TRUE,
      ])->toString();
      // Avoid redirection caching in upstream proxies.
      header("Cache-Control: must-revalidate, no-cache, post-check=0, pre-check=0, private");
    }

    if (isset($location)) {
      header("Location: $location");
      exit;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function fetch($server, $remote_file_dir, $relative_path, array $options) {
    try {
      // Fetch remote file.
      $url = $server . '/' . UrlHelper::encodePath($remote_file_dir . '/' . $relative_path);
      $options['Connection'] = 'close';
      $response = $this->client->get($url, $options);

      $result = $response->getStatusCode();
      if ($result != 200) {
        $this->getLogger('StageFileProxyProxySubscriber')->warning('HTTP error @errorcode occurred when trying to fetch @remote.', [
          '@errorcode' => $result,
          '@remote' => $url,
        ]);
        return FALSE;
      }

      // Prepare local target directory and save downloaded file.
      $file_dir = BrandFilesStreamWrapper::basePath();
      $destination = $file_dir . '/' . dirname($relative_path);
      if (!$this->fileSystem->prepareDirectory($destination, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS)) {
        $this->getLogger('StageFileProxyProxySubscriber')->error('Unable to prepare local directory @path.', ['@path' => $destination]);
        return FALSE;
      }

      $destination = str_replace('///', '//', "$destination/") . $this->fileSystem->basename($relative_path);

      $response_headers = $response->getHeaders();
      $content_length = array_shift($response_headers['Content-Length']);
      $response_data = $response->getBody()->getContents();
      if (isset($content_length) && strlen($response_data) != $content_length) {
        $this->getLogger('StageFileProxyProxySubscriber')->error('Incomplete download. Was expecting @content-length bytes, actually got @data-length.', [
          '@content-length' => $content_length,
          '@data-length' => $content_length,
        ]);
        return FALSE;
      }

      if ($this->writeFile($destination, $response_data)) {
        return TRUE;
      }

      $this->getLogger('StageFileProxyProxySubscriber')->error('@remote could not be saved to @path.', [
        '@remote' => $url,
        '@path' => $destination,
      ]);

      return FALSE;
    }
    catch (GuzzleException $e) {
      // Do nothing.
    }

    $this->getLogger('StageFileProxyProxySubscriber')->error('Stage File Proxy encountered an unknown error by retrieving file @file', ['@file' => $server . '/' . UrlHelper::encodePath($remote_file_dir . '/' . $relative_path)]);
    return FALSE;
  }

  /**
   * Use write & rename instead of write.
   *
   * Perform the replace operation. Since there could be multiple processes
   * writing to the same file, the best option is to create a temporary file in
   * the same directory and then rename it to the destination. A temporary file
   * is needed if the directory is mounted on a separate machine; thus ensuring
   * the rename command stays local.
   *
   * @param string $destination
   *   A string containing the destination location.
   * @param string $data
   *   A string containing the contents of the file.
   *
   * @return bool
   *   True if write was successful. False if write or rename failed.
   */
  protected function writeFile($destination, $data) {
    // Get a temporary filename in the destination directory.
    $dir = $this->fileSystem->dirname($destination) . '/';
    $temporary_file = $this->fileSystem->tempnam($dir, 'stage_file_proxy_');
    $temporary_file_copy = $temporary_file;

    // Get the extension of the original filename and append it to the temp file
    // name. Preserves the mime type in different stream wrapper
    // implementations.
    $parts = pathinfo($destination);
    $extension = '.' . $parts['extension'];
    if ($extension === '.gz') {
      $parts = pathinfo($parts['filename']);
      $extension = '.' . $parts['extension'] . $extension;
    }
    // Move temp file into the destination dir if not in there.
    // Add the extension on as well.
    $temporary_file = str_replace(substr($temporary_file, 0, strpos($temporary_file, 'stage_file_proxy_')), $dir, $temporary_file) . $extension;

    // Preform the rename, adding the extension to the temp file.
    if (!@rename($temporary_file_copy, $temporary_file)) {
      // Remove if rename failed.
      @unlink($temporary_file_copy);
      return FALSE;
    }

    // Save to temporary filename in the destination directory.
    $filepath = $this->fileSystem->saveData($data, $temporary_file, FileSystemInterface::EXISTS_REPLACE);

    // Perform the rename operation if the write succeeded.
    if ($filepath) {
      if (!@rename($filepath, $destination)) {
        // Unlink and try again for windows. Rename on windows does not replace
        // the file if it already exists.
        @unlink($destination);
        if (!@rename($filepath, $destination)) {
          // Remove temporary_file if rename failed.
          @unlink($filepath);
        }
      }
    }

    // Final check; make sure file exists & is not empty.
    $result = FALSE;
    if (file_exists($destination) & filesize($destination) != 0) {
      $result = TRUE;
    }
    return $result;
  }

}
