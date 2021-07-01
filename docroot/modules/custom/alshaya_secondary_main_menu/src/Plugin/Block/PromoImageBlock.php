<?php

namespace Drupal\alshaya_secondary_main_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\menu_link_content\Plugin\Menu\MenuLinkContent;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Cache\Cache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Entity\EntityRepositoryInterface;

/**
 * Provides a 'PromoMenu' block.
 *
 * @Block(
 *   id = "promo_menu_images",
 *   admin_label = @Translation("Promo Menu Images"),
 * )
 */
class PromoImageBlock extends BlockBase implements ContainerFactoryPluginInterface {
  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;
  /**
   * Module Handler service object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;
  /**
   * The menu link tree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuTree;
  /**
   * Entity repository.
   *
   * @var Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * AlshayaSecondaryMenuBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_tree
   *   The menu tree service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service object.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   Entity repository.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, MenuLinkTreeInterface $menu_tree, ModuleHandlerInterface $module_handler, EntityRepositoryInterface $entityRepository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->menuTree = $menu_tree;
    $this->moduleHandler = $module_handler;
    $this->entityRepository = $entityRepository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('menu.link_tree'),
      $container->get('module_handler'),
      $container->get('entity.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $menu_name = 'promo-menu';
    $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters($menu_name);
    $tree = $this->menuTree->load($menu_name, $parameters);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $this->menuTree->transform($tree, $manipulators);
    $promo_menu = $this->menuTree->build($tree);
    // If no data, no need to render the block.
    if (empty($promo_menu['#items'])) {
      return [];
    }
    foreach ($promo_menu['#items'] as $item) {
      if ($item['original_link'] instanceof MenuLinkContent) {
        $uuid = $item['original_link']->getDerivativeId();
        $entity = $this->entityRepository->loadEntityByUuid('menu_link_content', $uuid);
        $item['promo_paragraph'] = $entity->get('field_promo_images')->getValue();
        foreach ($item['promo_paragraph'] as $tr) {
          $paragraph = Paragraph::load($tr['target_id']);
          $link_to_image = $paragraph->field_link_to_image->getValue();
          $img_title = $paragraph->get('field_image_title')->value;
          $promo_menu['#items']['promo_buttons'][] =
            [
              'imgurl' => $paragraph->get('field_promo_image')->entity->uri->value,
              'link_title' => $link_to_image[0]['title'],
              'link' => Url::fromUri($link_to_image[0]['uri']),
              'img_title' => $img_title,
            ];
        }
      }
    }
    return [
      '#theme' => 'alshaya_secondary_promo_image',
      '#promo' => $promo_menu['#items']['promo_buttons'],
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
    $cache_tags = parent::getCacheTags();
    $cache_tags[] = 'config:system.menu.promo-menu';
    return $cache_tags;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // ::build() uses MenuLinkTreeInterface::getCurrentRouteMenuTreeParameters()
    // to generate menu tree parameters, and those take the active menu trail
    // into account. Therefore, we must vary the rendered menu by the active
    // trail of the rendered menu.
    // Additional cache contexts, e.g. those that determine link text or
    // accessibility of a menu, will be bubbled automatically.
    return Cache::mergeContexts(parent::getCacheContexts(), ['route.menu_active_trails:' . 'promo-menu']);
  }

}
