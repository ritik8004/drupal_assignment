<?php

namespace Drupal\alshaya_menus\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Database\Connection;

/**
 * Provides a generic Menu block.
 *
 * @Block(
 *   id = "alshaya_shop_by_block",
 *   admin_label = @Translation("Shop by"),
 * )
 */
class AlshayaShopByBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * AlshayaShopByBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
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
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->configuration;

    $defaults = $this->defaultConfiguration();
    $form['shop_by_voc'] = [
      '#type' => 'details',
      '#title' => $this->t('Shop By Vocabulary'),
      '#open' => TRUE,
    ];
    $vocabularies = taxonomy_vocabulary_get_names();
    $form['shop_by_voc']['vocabulary'] = [
      '#type' => 'select',
      '#title' => $this->t('Vocabulary'),
      '#default_value' => $config['level'],
      '#options' => $vocabularies,
      '#description' => $this->t('The Vocabulary that we want to show in shop by links.'),
      '#required' => TRUE,
    ];

    $options = range(0, 10);
    unset($options[0]);
    $form['shop_by_voc']['level'] = [
      '#type' => 'select',
      '#title' => $this->t('Initial visibility level'),
      '#default_value' => $config['level'],
      '#options' => $options,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['vocabulary'] = $form_state->getValue(['shop_by_voc', 'vocabulary']);
    $this->configuration['level'] = $form_state->getValue(['shop_by_voc', 'level']);
  }

  /**
   * Get the list of all terms based on config saved for block.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]|null
   *   Return loaded terms.
   */
  protected function getTermsTree() {
    $query = $this->connection->select('taxonomy_term_field_data', 'fd');
    $query->fields('fd', ['tid', 'weight', 'langcode']);
    $query->join('taxonomy_term__field_category_include_menu', 'im', 'fd.tid = im.entity_id and im.deleted = 0 and fd.langcode = im.langcode');
    $query->condition('fd.vid', $this->configuration['vocabulary']);
    $query->condition('fd.depth_level', $this->configuration['level']);
    $query->condition('fd.langcode', $this->languageManager->getDefaultLanguage()->getId());
    $query->condition('im.field_category_include_menu_value', 1);
    $query->orderBy('fd.weight');
    $result = $query->execute()->fetchAll(\PDO::FETCH_COLUMN);
    if ($result) {
      return Term::loadMultiple($result);
    }
    return NULL;
  }

  /**
   * Process the translation of the given term.
   *
   * @param object $term
   *   The term object.
   *
   * @return array
   *   Return the processed array be used to render the output.
   */
  protected function processTermTranslations($term) {
    // For language specific data.
    $term = $this->entityRepository->getTranslationFromContext($term);

    // For cache tag bubbling up.
    $this->cacheTags[] = 'taxonomy_term:' . $term->id();

    $build = [
      'label' => $term->label(),
      'description' => $term->getDescription(),
      'id' => $term->id(),
      'path' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $term->id()])->toString(),
      'active_class' => '',
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /* @var \Drupal\taxonomy\TermInterface[] $terms */
    $terms = $this->getTermsTree();

    // If no data, no need to render the block.
    if (empty($terms)) {
      return [
        '#markup' => '',
      ];
    }

    $data = [];
    foreach ($terms as $term) {
      $data[$term->id()] = $this->processTermTranslations($term);
    }

    $route_name = $this->routeMatch->getRouteName();
    // If /taxonomy/term/tid page.
    if ($route_name == 'entity.taxonomy_term.canonical') {
      /* @var \Drupal\taxonomy\TermInterface $route_parameter_value */
      $route_parameter_value = $this->routeMatch->getParameter('taxonomy_term');
      // If term is of 'acq_product_category' vocabulary.
      if ($route_parameter_value->getVocabularyId() == $this->configuration['vocabulary']) {
        // Get all parents of the given term.
        $parents = $this->entityManager->getStorage('taxonomy_term')->loadAllParents($route_parameter_value->id());

        if (!empty($parents)) {
          /* @var \Drupal\taxonomy\TermInterface $root_parent_term */
          $root_parent_term = end($parents);
          if (isset($data[$root_parent_term->id()])) {
            $data[$root_parent_term->id()]['active_class'] = 'active';
          }
        }
      }
    }

    return [
      '#theme' => 'alshaya_shop_by',
      '#term_tree' => $data,
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
  public function defaultConfiguration() {
    return [
      'level' => 1,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Even when the menu block renders to the empty string for a user, we want
    // the cache tag for this menu to be set: whenever the menu is changed, this
    // menu block must also be re-rendered for that user, because maybe a menu
    // link that is accessible for that user has been added.
    return Cache::mergeTags(
      parent::getCacheTags(),
      $this->cacheTags
    );
  }

}
