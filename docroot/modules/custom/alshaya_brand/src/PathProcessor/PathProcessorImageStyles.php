<?php

namespace Drupal\alshaya_brand\PathProcessor;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a path processor to rewrite image styles URLs.
 *
 * As the route system does not allow arbitrary amount of parameters convert
 * the file path to a query parameter on the request.
 *
 * This processor handles two different cases:
 * - brand image styles: In order to allow the webserver to serve these files
 *   directly, the route is registered under the same path as the image style so
 *   it took over the first generation. Therefore the path processor converts
 *   the file path to a query parameter.
 */
class PathProcessorImageStyles implements InboundPathProcessorInterface {

  /**
   * The stream wrapper manager service.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface
   */
  protected $streamWrapperManager;

  /**
   * Constructs a new PathProcessorImageStyles object.
   *
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface $stream_wrapper_manager
   *   The stream wrapper manager service.
   */
  public function __construct(StreamWrapperManagerInterface $stream_wrapper_manager) {
    $this->streamWrapperManager = $stream_wrapper_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {
    $directory_path = $this->streamWrapperManager->getViaScheme('brand')->getDirectoryPath();

    if (str_starts_with($path, '/' . $directory_path . '/styles/')) {
      $path_prefix = '/' . $directory_path . '/styles/';

      // Strip out path prefix.
      $rest = preg_replace('|^' . preg_quote($path_prefix, '|') . '|', '', $path);

      // Get the image style, scheme and path.
      if (substr_count($rest, '/') >= 2) {
        [$image_style, $scheme, $file] = explode('/', $rest, 3);

        // Set the file as query parameter.
        $request->query->set('file', $file);

        return $path_prefix . $image_style . '/' . $scheme;
      }
    }

    return $path;
  }

}
