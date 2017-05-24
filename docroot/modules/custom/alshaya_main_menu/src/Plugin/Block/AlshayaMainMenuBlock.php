<?php

namespace Drupal\alshaya_main_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\paragraphs\Entity\Paragraph;
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
    // If /taxonomy/term/tid page.
    if ($route_name == 'entity.taxonomy_term.canonical') {
      /* @var \Drupal\taxonomy\TermInterface $route_parameter_value */
      $route_parameter_value = $this->routeMatch->getParameter('taxonomy_term');
      // If term is of 'acq_product_category' vocabulary.
      if ($route_parameter_value->getVocabularyId() == 'acq_product_category') {
        // Get all parents of the given term.
        $parents = $this->entityManager->getStorage('taxonomy_term')->loadAllParents($route_parameter_value->id());

        if (!empty($parents)) {
          /* @var \Drupal\taxonomy\TermInterface $root_parent_term */
          $root_parent_term = end($parents);
          if (isset($term_data[$root_parent_term->id()])) {
            $term_data[$root_parent_term->id()]['class'] = 'active';
          }
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
   * Prepares the tree structure of the terms.
   *
   * @param int $parent_tid
   *   The parent tid.
   * @param int $depth
   *   Depth of term to find.
   *
   * @return array
   *   Tree structure of terms.
   */
  protected function getChildTerms($parent_tid = 0, $depth = 1) {
    $data = [];

    /* @var \Drupal\taxonomy\TermInterface[] $terms */
    $terms = $this->entityManager->getStorage('taxonomy_term')->loadTree('acq_product_category', $parent_tid, $depth, TRUE);
    if ($terms) {
      foreach ($terms as $term) {
        // For language specific data.
        $term = $this->entityRepository->getTranslationFromContext($term);

        // For cache tag bubbling up.
        $this->cacheTags[] = 'taxonomy_term:' . $term->id();

        // Get value of boolean field which will decide if we show/hide this
        // term and child terms in the menu.
        $include_in_menu = $term->get('field_category_include_menu')->getValue();

        // Hide the menu if there is a value in the field and it is FALSE.
        if (!empty($include_in_menu) && !($include_in_menu[0]['value'])) {
          continue;
        }

        $data[$term->id()] = [
          'label' => $term->label(),
          'description' => $term->getDescription(),
          'id' => $term->id(),
          'path' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $term->id()])->toString(),
          'highlight_image' => $this->getHighlightImage($term),
          'active_class' => '',
        ];

        // Check if there is a department page available for this term.
        if ($nid = alshaya_department_page_page_exists($term->id())) {
          // Use the path of node instead of term path.
          $data[$term->id()]['path'] = Url::fromRoute('entity.node.canonical', ['node' => $nid])->toString();
        }

        $data[$term->id()]['child'] = $this->getChildTerms($term->id());
      }
    }

    return $data;
  }

  /**
   * Get the highlight image for the given term.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   Term object.
   *
   * @return array
   *   Array of image url.
   */
  protected function getHighlightImage(TermInterface $term) {
    $highlight_images = [];

    if ($highlight_field = $term->get('field_main_menu_highlight')) {

      // If no data in paragraph referenced field.
      if (empty($highlight_field->getValue())) {
        return $highlight_images;
      }

      foreach ($highlight_field->getValue() as $paragraph_id) {
        $paragraph_id = $paragraph_id['target_id'];

        // Load paragraph entity.
        $paragraph = Paragraph::load($paragraph_id);

        // Get the current language code.
        $language = $this->languageManager->getCurrentLanguage()->getId();

        // Get the translation of the paragraph if exists.
        if ($paragraph->hasTranslation($language)) {
          // Replace the current paragraph with translated one.
          $paragraph = $paragraph->getTranslation($language);
        }

        if ($paragraph && !empty($paragraph->get('field_highlight_image'))) {
          $image = $paragraph->get('field_highlight_image')->getValue();
          $image_link = $paragraph->get('field_highlight_link')->getValue();
          if (!empty($image)) {
            $file = File::load($image[0]['target_id']);
            $url = Url::fromUri($image_link[0]['uri']);
            $highlight_images[] = [
              'image_link' => $url->toString(),
              'img' => file_create_url($file->getFileUri()),
            ];
          }
        }
      }
    }

    return $highlight_images;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Processed vocabulary data.
    $this->termData = $this->getChildTerms();

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
