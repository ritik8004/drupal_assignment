<?php

namespace Drupal\alshaya_main_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides alshaya main menu block.
 *
 * @Block(
 *   id = "alshaya_main_menu",
 *   admin_label = @Translation("Alshaya main menu")
 * )
 */
class AlshayaMainMenuBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * The entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Vocabulary processed data.
   *
   * @var array
   */
  protected $termData = [];

  /**
   * Array of terms for cache bubbling up.
   *
   * @var array
   */
  protected $cacheTags = [];

  /**
   * Route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * AlshayaMegaMenuBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin defination.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match service.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language
   *   The Language manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_manager, EntityRepositoryInterface $entity_repository, RouteMatchInterface $route_match, Connection $connection, LanguageManagerInterface $language) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityManager = $entity_manager;
    $this->entityRepository = $entity_repository;
    $this->routeMatch = $route_match;
    $this->connection = $connection;
    $this->languageManager = $language;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity.repository'),
      $container->get('current_route_match'),
      $container->get('database'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $term_data = $this->termData;

    // If no data, no need to render the block.
    if (empty($term_data)) {
      return [
        '#markup' => '',
      ];
    }

    $route_name = $this->routeMatch->getRouteName();
    $term = NULL;
    // If /taxonomy/term/tid page.
    if ($route_name == 'entity.taxonomy_term.canonical') {
      /* @var \Drupal\taxonomy\TermInterface $route_parameter_value */
      $term = $this->routeMatch->getParameter('taxonomy_term');
    }
    // If it's a department page.
    elseif ($route_name == 'entity.node.canonical') {
      $node = $this->routeMatch->getParameter('node');
      if ($node->bundle() == 'department_page') {
        $terms = $node->get('field_product_category')->getValue();
        $term = $this->entityManager->getStorage('taxonomy_term')->load($terms[0]['target_id']);
      }
    }

    // If term is of 'acq_product_category' vocabulary.
    if (is_object($term) && $term->getVocabularyId() == 'acq_product_category') {
      // Get all parents of the given term.
      $parents = $this->entityManager->getStorage('taxonomy_term')->loadAllParents($term->id());

      if (!empty($parents)) {
        /* @var \Drupal\taxonomy\TermInterface $root_parent_term */
        $root_parent_term = end($parents);
        if (isset($term_data[$root_parent_term->id()])) {
          $term_data[$root_parent_term->id()]['class'] = 'active';
        }
      }
    }

    return [
      '#theme' => 'alshaya_main_menu_level1',
      '#term_tree' => $term_data,
      '#cache' => [
        'contexts' => [
          'url.path',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Processed vocabulary data.
    $data = _alshaya_custom_get_category_term_data(0, 1, TRUE);
    $this->termData = $data;

    return Cache::mergeTags(
      parent::getCacheTags(),
      $this->cacheTags
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // Add department page node type cache tag.
    // This is custom cache tag and cleared in hook_presave in department
    // module.
    $this->cacheTags[] = 'node_type:department_page';
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

}
