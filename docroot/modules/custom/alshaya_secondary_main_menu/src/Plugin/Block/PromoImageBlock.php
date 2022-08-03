<?php

namespace Drupal\alshaya_secondary_main_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\menu_link_content\Plugin\Menu\MenuLinkContent;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Cache\Cache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Provides a 'PromoMenu' block.
 *
 * @Block(
 *   id = "promo_menu_images",
 *   admin_label = @Translation("Promo Menu Images"),
 * )
 */
class PromoImageBlock extends BlockBase implements ContainerFactoryPluginInterface {
  public const MENU_NAME = 'promo-menu';
  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;
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
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

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
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   Entity repository.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, MenuLinkTreeInterface $menu_tree, EntityRepositoryInterface $entityRepository, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->menuTree = $menu_tree;
    $this->entityRepository = $entityRepository;
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
      $container->get('config.factory'),
      $container->get('menu.link_tree'),
      $container->get('entity.repository'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters(self::MENU_NAME);
    $tree = $this->menuTree->load(self::MENU_NAME, $parameters);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $this->menuTree->transform($tree, $manipulators);
    $promo_menu = $this->menuTree->build($tree);
    $language = $this->languageManager->getCurrentLanguage()->getId();
    $default_lang = $this->languageManager->getDefaultLanguage()->getId();
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
          if ($language != $default_lang && !($paragraph->hasTranslation($language))) {
            $paragraph = $paragraph->getTranslation($default_lang);
          }
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
    return Cache::mergeContexts(parent::getCacheContexts(), ['route.menu_active_trails:promo-menu']);
  }

}
