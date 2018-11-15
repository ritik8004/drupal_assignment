<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\views\Views;
use Drupal\Core\Render\RendererInterface;
use Psr\Log\LoggerInterface;

/**
 * Class StoresFinderResource.
 *
 * @RestResource(
 *   id = "stores_finder",
 *   label = @Translation("Stores Finder"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/store/search/{lat}/{lng}",
 *   }
 * )
 */
class StoresFinderResource extends ResourceBase {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * SimplePageResource constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param array $serializer_formats
   *   Serializer formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger channel.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    RendererInterface $renderer
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('alshaya_mobile_app'),
      $container->get('renderer')
    );
  }

  /**
   * Responds to GET requests.
   *
   * Returns available store id's.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing store id's data.
   */
  public function get($lat, $lng) {
    $response_data = [];

    // Get store finder view.
    $view = Views::getView('stores_finder');
    if (!empty($view)) {
      // Set the view display to page_1.
      $view->setDisplay('page_1');
      $proximity_handler = $view->getHandler('page_1', 'filter', 'field_latitude_longitude_proximity');
      $input = [
        'field_latitude_longitude_proximity-lat' => $lat,
        'field_latitude_longitude_proximity-lng' => $lng,
        'field_latitude_longitude_proximity' => $proximity_handler['value']['value'] ?: 5,
      ];
      // Set exposed form input values.
      $view->setExposedInput($input);
      $view_render_array = NULL;
      $rendered_view = NULL;
      $this->renderer->executeInRenderContext(new RenderContext(), function () use ($view, &$view_render_array, &$rendered_view) {
        $view_render_array = $view->render();
        $rendered_view = render($view_render_array);
      });
      foreach ($view->result as $row) {
        $response_data[] = $row->_entity->get('field_store_locator_id')->getValue()[0]['value'];
      }
      $response = new ResourceResponse($response_data);
      $response->addCacheableDependency(CacheableMetadata::createFromRenderArray($view_render_array));
      return $response;
    }

    return (new ModifiedResourceResponse($response_data));
  }

}
