<?php

namespace Drupal\alshaya_facets_pretty_paths\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Url;
use Drupal\facets\FacetSource\FacetSourcePluginManager;
use Symfony\Component\Routing\RouteCollection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\Routing\Route;
use Drupal\Core\Routing\RoutingEvents;

/**
 * Alter facet source routes, adding a parameter.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * Service plugin.manager.facet_source.
   *
   * @var \Drupal\facets\FacetSource\FacetSourcePluginManager
   */
  protected $facetSourcePluginManager;


  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a RouteSubscriber object.
   *
   * @param Drupal\facets\FacetSource\FacetSourcePluginManager $facetSourcePluginManager
   *   The plugin.manager.facets.facet_source service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   */
  public function __construct(FacetSourcePluginManager $facetSourcePluginManager, EntityTypeManagerInterface $entity_type_manager) {
    $this->facetSourcePluginManager = $facetSourcePluginManager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = parent::getSubscribedEvents();
    // Ensure to run after
    // \Drupal\views\Plugin\views\display\PathPluginBase::alterRoutes
    // Hence we need to trigger this after
    // \Drupal\views\EventSubscriber\RouteSubscriber::alterRoutes.
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -200];

    return $events;
  }

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    $sources = $this->facetSourcePluginManager->getDefinitions();
    foreach ($sources as $source) {
      $sourcePlugin = $this->facetSourcePluginManager->createInstance($source['id']);
      $path = $sourcePlugin->getPath();

      $storage = $this->entityTypeManager->getStorage('facets_facet_source');
      $source_id = str_replace(':', '__', $sourcePlugin->getPluginId());
      $facet_source = $storage->load($source_id);
      if (!$facet_source || $facet_source->getUrlProcessorName() != 'alshaya_facets_pretty_paths') {
        // If no custom configuration is set for the facet source, it is not
        // using pretty_paths. If there is custom configuration, ensure the url
        // processor is pretty paths.
        continue;
      }

      try {
        if ($path !== '/search') {
          $routeName = 'entity.taxonomy_term.canonical';
        }

        if (!isset($routeName)) {
          $url = Url::fromUri('internal:' . $path);
          $routeName = $url->getRouteName();
        }

        $sourceRoute = $collection->get($routeName);

        if ($sourceRoute) {
          $originalPath = $sourceRoute->getPath();
          if (!strpos($originalPath, '{facets_query}')) {
            // Remove the existing route object so that we can rebuild.
            $collection->remove($routeName);
            // Building new route object with {facets_query} param.
            $newFacetRoute = new Route($originalPath . '/{facets_query}', $sourceRoute->getDefaults());
            $newFacetRoute->setMethods($sourceRoute->getMethods());
            $newFacetRoute->setDefault('facets_query', '');
            $newFacetRoute->setRequirements($sourceRoute->getRequirements());
            $newFacetRoute->setRequirement('facets_query', '.*');
            $newFacetRoute->setOptions($sourceRoute->getOptions());
            // Assigning the new route object to same route name.
            $collection->add($routeName, $newFacetRoute);
          }
        }
      }
      catch (\Exception) {

      }

    }

  }

}
