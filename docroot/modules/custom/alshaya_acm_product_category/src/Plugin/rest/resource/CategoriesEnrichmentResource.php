<?php

namespace Drupal\alshaya_acm_product_category\Plugin\rest\resource;

use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_acm_product_category\Event\GetEnrichedCategoryDataEvent;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Provides a resource to get list of all enriched categories.
 *
 * @RestResource(
 *   id = "categories_enrichment",
 *   label = @Translation("List all categories with enrichment data"),
 *   uri_paths = {
 *     "canonical" = "/rest/v3/categories"
 *   }
 * )
 */
class CategoriesEnrichmentResource extends ResourceBase {

  /**
   * Event dispatcher.
   *
   * @var \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher
   */
  protected $eventDispatcher;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * CategoriesEnrichmentResource constructor.
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
   * @param \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher $event_dispatcher
   *   Event dispatcher.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              array $serializer_formats,
                              LoggerInterface $logger,
                              ContainerAwareEventDispatcher $event_dispatcher,
                              LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->eventDispatcher = $event_dispatcher;
    $this->languageManager = $language_manager;
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
      $container->get('logger.factory')->get('alshaya_acm_product_category'),
      $container->get('event_dispatcher'),
      $container->get('language_manager')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing list of categories.
   */
  public function get() {
    // We pass language from here so that we do not have to decide the language
    // source individually in other places. We mainly use this to fetch
    // categories for a certain langcode.
    $event = new GetEnrichedCategoryDataEvent($this->languageManager->getCurrentLanguage()->getId());
    $this->eventDispatcher->dispatch(GetEnrichedCategoryDataEvent::EVENT_NAME, $event);
    $response = new ResourceResponse($event->getData());
    $response->addCacheableDependency($event->getCacheabilityMetadata());

    return $response;
  }

}
