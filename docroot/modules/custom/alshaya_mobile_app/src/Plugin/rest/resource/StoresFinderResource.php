<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\views\Views;

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('alshaya_mobile_app')
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
      $view->execute();
      foreach ($view->result as $row) {
        $response_data[] = $row->_entity->get('field_store_locator_id')->getValue()[0]['value'];
      }
    }

    return (new ModifiedResourceResponse($response_data));
  }

}
