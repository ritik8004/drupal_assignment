<?php

namespace Drupal\alshaya_rcs_super_category\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\alshaya_super_category\AlshayaSuperCategoryManager;
use Drupal\Core\Theme\ThemeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\Core\Url;
use Drupal\alshaya_rcs_super_category\Service\AlshayaRcsSuperCategoryManager;
use Drupal\alshaya_rcs_super_category\Service\RcsProductCategoryTree;

/**
 * Provides alshaya rcs super category menu block.
 *
 * @Block(
 *   id = "alshaya_rcs_super_category_menu",
 *   admin_label = @Translation("Alshaya Rcs super category menu")
 * )
 */
class AlshayaRcsSuperCategoryBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The request object.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Super Category manager service.
   *
   * @var \Drupal\alshaya_rcs_super_category\Service\AlshayaRcsSuperCategoryManager
   */
  protected $superCategoryManager;

  /**
   * Super Category Tree service.
   *
   * @var \Drupal\alshaya_rcs_super_category\Service\RcsProductCategoryTree
   */
  protected $productCategoryTree;


  /**
   * AlshayaSuperCategoryBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   Language manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param Symfony\Component\HttpFoundation\RequestStack $request
   *   Entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager service object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\alshaya_rcs_super_category\Service\AlshayaRcsSuperCategoryManager $super_category_manager
   *   Super Category Manager.
   * @param \Drupal\alshaya_rcs_super_category\Service\RcsProductCategoryTree $product_category_tree
   *   Super Category Tree service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ThemeManagerInterface $theme_manager,
    ConfigFactoryInterface $config_factory,
    RequestStack $request,
    EntityTypeManagerInterface $entity_type_manager,
    LanguageManagerInterface $language_manager,
    AlshayaRcsSuperCategoryManager $super_category_manager,
    RcsProductCategoryTree $product_category_tree
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->themeManager = $theme_manager;
    $this->configFactory = $config_factory;
    $this->request = $request;
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->superCategorymanager = $super_category_manager;
    $this->productCategoryTree = $product_category_tree;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('theme.manager'),
      $container->get('config.factory'),
      $container->get('request_stack'),
      $container->get('entity_type.manager'),
      $container->get('language_manager'),
      $container->get('alshaya_super_category.super_category_feature_manager'),
      $container->get('alshaya_acm_product_category.product_category_tree')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Don't need to build this block if status of super category settings
    // is false.
    if (!$this->configFactory->get('alshaya_super_category.settings')->get('status')) {
      return [];
    }
    $lang_code = $this->languageManager->getCurrentLanguage()->getId();
    $current_super_term = \Drupal::service('alshaya_acm_product_category.product_category_tree')->getCategoryTermFromRoute();
    $current_tid = ($current_super_term instanceof TermInterface)
      ? $current_super_term->id()
      : NULL;

    $placeholder_tid = $this->configFactory->get('rcs_placeholders.settings')->get('category.placeholder_tid');

    // Load L1 supercategories.
    $super_categories =$this->entityTypeManager->getStorage('taxonomy_term')->loadTree('rcs_category', 0, 1, TRUE);
    $term_data = [];
    foreach($super_categories as $category) {
      if ($category->id() === $placeholder_tid) {
        continue;
      }
      $category_en = ($category->language()->getId() === 'en')
        ? $category
        : $category->getTranslation('en');
      $class = ' brand-' . Html::cleanCssIdentifier(mb_strtolower($category_en->getName()));
      $gtm_menu_title = NULL;
      if ($current_tid === $category->id()
      ) {
        $class .= ' active';
        $gtm_menu_title =
         $category_en->getName();
      }

      // Get brand icons of supercategory.
      $img_path = $inactive_path = NULL;
      $mdc_id = $category->get('field_commerce_id')->getString();
      $brand_icons = $this->productCategoryTree->getBrandIcons($mdc_id);
      if ((isset($brand_icons['active_image']) && !empty($brand_icons['active_image']))
      && (isset($brand_icons['inactive_image']) && !empty($brand_icons['inactive_image']))) {
        $img_path = (str_contains($class, 'active'))
        ? $brand_icons['active_image']
        : $brand_icons['inactive_image'];
        $inactive_path = $brand_icons['active_image'];
      }

      $term_data[$mdc_id] = [
        'label' => $category->getName(),
        'meta_title' => $category->getName(),
        'class' => $class,
        'gtm_menu_title' => $gtm_menu_title,
        'imgPath' => $img_path,
        'inactive_path' => $inactive_path,
        'path' => '/' . $category->get('field_category_slug')->getString(),
      ];
    }

    // Set the default parent from settings.
    $parent_id = $this->superCategorymanager->getDefaultCategoryId();

    // Set default category link to redirect to home page.
    // Default category is set to active, while we are on home page.
    if (isset($term_data[$parent_id])) {
      $term_data[$parent_id]['path'] = Url::fromRoute('<front>')->toString();
    }

    return [
      '#theme' => 'alshaya_super_category_top_level',
      '#term_tree' => $term_data,
      '#attributes' => [
        'class' => [
          'block-alshaya-super-category-menu',
          'block-alshaya-super-category',
        ],
      ],
      '#attached' => [
        'library' => [
          'alshaya_rcs_main_menu/renderer',
          'alshaya_super_category/minimalistic_header',
        ],
        'drupalSettings' => [
          'superCategory' => [
            'search_facet' => AlshayaSuperCategoryManager::SEARCH_FACET_NAME,
          ],
          'theme' => [
            'path' => $this->themeManager->getActiveTheme()->getPath(),
          ],
        ],
      ],
      '#cache' => ['max-age' => 0],
    ];
  }

}
