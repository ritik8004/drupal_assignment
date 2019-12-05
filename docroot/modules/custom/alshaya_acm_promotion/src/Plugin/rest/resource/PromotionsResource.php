<?php

namespace Drupal\alshaya_acm_promotion\Plugin\rest\resource;

use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\node\NodeInterface;
use Drupal\rest\ModifiedResourceResponse;

/**
 * Provides a resource to get list of all promotions.
 *
 * @RestResource(
 *   id = "promotions",
 *   label = @Translation("List all promotions"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/promotion/all"
 *   }
 * )
 */
class PromotionsResource extends ResourceBase {

  /**
   * Node bundle machine name.
   */
  const NODE_BUNDLE = 'acq_promotion';

  /**
   * The content to be cached.
   *
   * @var array
   */
  protected $content = [];

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * PromotionsResource constructor.
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
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              array $serializer_formats,
                              LoggerInterface $logger,
                              LanguageManagerInterface $language_manager,
                              EntityTypeManagerInterface $entity_type_manager,
                              Connection $connection,
                              ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->connection = $connection;
    $this->moduleHandler = $module_handler;
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
      $container->get('logger.factory')->get('alshaya_acm_promotion'),
      $container->get('language_manager'),
      $container->get('entity_type.manager'),
      $container->get('database'),
      $container->get('module_handler')
    );
  }

  /**
   * Get the list of node ids of promotion.
   *
   * @param string $langcode
   *   The language code.
   *
   * @return array
   *   List of nids.
   */
  private function getAllPromotions($langcode) {
    $query = $this->connection->select('node', 'n');
    $query->fields('nd', ['nid']);
    $query->leftJoin('node_field_data', 'nd', 'nd.nid = n.nid');
    $query->condition('nd.langcode', $langcode);
    $query->condition('nd.type', 'acq_promotion');
    $query->condition('nd.status', NodeInterface::PUBLISHED);
    return $query->execute()->fetchCol();
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing list of categories.
   */
  public function get() {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $nids = $this->getAllPromotions($langcode);
    $response_data = [];
    if (!empty($nids)) {
      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

      // Get the active promotion settings from "alshaya_cart_promotions_block".
      $blocks = $this->entityTypeManager->getStorage('block')->loadByProperties(['plugin' => 'alshaya_cart_promotions_block', 'status' => TRUE]);
      $block = reset($blocks);
      $active_promotions = !empty($block->get('settings')['promotions'])
        ? array_filter($block->get('settings')['promotions'])
        : [];
      $tag = "config:{$this->entityTypeManager->getDefinition('block')->getConfigPrefix()}.{$block->id()}";
      // Check if the current language is arabic.
      $default_language = $this->languageManager->getDefaultLanguage()->getId();
      $getTranslatedNode = ($langcode !== $default_language);

      foreach ($nodes as $node) {
        if ($getTranslatedNode && $node->hasTranslation($langcode)) {
          $node = $node->getTranslation($langcode);
        }
        // Get bubbleable metadata for CacheableDependency to avoid fatal error.
        $node_url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString(TRUE);

        $data = [
          'id' => (int) $node->id(),
          'name' => $node->label(),
          'path' => $node_url->getGeneratedUrl(),
          'commerce_id' => (int) $node->get('field_acq_promotion_rule_id')->first()->getString(),
          'promote' => in_array($node->get('field_acq_promotion_rule_id')->first()->getString(), $active_promotions),
          'promo_sub_tpe' => $node->get('field_alshaya_promotion_subtype')->first()->getString(),
          'promo_desc' => $node->get('field_acq_promotion_description')->first() ? $node->get('field_acq_promotion_description')->first()->getString() : '',
          'promo_label' => $node->get('field_acq_promotion_label')->first()->getValue()['value'],
        ];

        $this->moduleHandler->alter('alshaya_acm_promo_resource', $data, $node);

        $response_data[] = $data;
        $this->content[] = $node;
      }

      $response = new ResourceResponse($response_data);
      $this->addCacheableDependency($response, $tag);
      return $response;
    }

    // Sending modified response so response is not cached when promotions
    // not available.
    return (new ModifiedResourceResponse($response_data));
  }

  /**
   * Adding nodes dependency to response.
   *
   * @param \Drupal\rest\ResourceResponse $response
   *   Response object.
   * @param string $tag
   *   The tag string.
   */
  protected function addCacheableDependency(ResourceResponse $response, $tag) {
    if (!empty($this->content)) {
      foreach ($this->content as $node) {
        $response->addCacheableDependency($node);
      }
    }

    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
      '#cache' => [
        'tags' => [$tag, 'node_list'],
      ],
    ]));
  }

}
