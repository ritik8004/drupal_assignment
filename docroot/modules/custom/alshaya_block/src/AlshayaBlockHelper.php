<?php

namespace Drupal\alshaya_block;

use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Menu\MenuLinkTree;
use Drupal\path_alias\AliasRepositoryInterface;
use Drupal\Core\Path\CurrentPathStack;

/**
 * Helper class to check if the current path belongs to given menu.
 */
class AlshayaBlockHelper {

  /**
   * The language manger.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * The Menu tree.
   *
   * @var \Drupal\Core\Menu\MenuLinkTree
   */
  protected $menuTree;

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * The alias storage.
   *
   * @var \Drupal\path_alias\AliasRepositoryInterface
   */
  protected $aliasStorage;

  /**
   * Creates a CustomLogoBlock instance.
   *
   * @param \Drupal\Core\Language\LanguageManager $language_manager
   *   The language manager.
   * @param \Drupal\Core\Menu\MenuLinkTree $menu_tree
   *   The menu link tree service.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path stack.
   * @param \Drupal\path_alias\AliasRepositoryInterface $alias_storage
   *   The alias storage service.
   */
  public function __construct(LanguageManager $language_manager, MenuLinkTree $menu_tree, CurrentPathStack $current_path, AliasRepositoryInterface $alias_storage) {
    $this->languageManager = $language_manager;
    $this->menuTree = $menu_tree;
    $this->currentPath = $current_path;
    $this->aliasStorage = $alias_storage;
  }

  /**
   * Check if the current path belongs to the given menu.
   *
   * If current path belongs to the given menu, return the array with active
   * link and active parent element else return empty array.
   *
   * @param string $menu_name
   *   The menu name. Default is main menu.
   *
   * @return array
   *   Return the array of active menu link or empty array.
   */
  public function checkCurrentPathInMenu($menu_name = 'main') {
    $output = [];
    // Get current language code.
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    // Get the given menu tree to get the current active path.
    $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters($menu_name);
    $parameters->setTopLevelOnly();
    $tree = $this->menuTree->load($menu_name, $parameters);

    // Retrieve an array which contains the path pieces.
    $current_path = $this->currentPath->getPath();
    // Get current path alias.
    $current_path_alias = $this->aliasStorage->load([
      'source' => $current_path,
      'langcode' => $langcode,
    ]);

    // Get the active link if any!.
    foreach ($tree as $element) {
      if ($element->inActiveTrail) {
        /** @var \Drupal\Core\Menu\MenuLinkInterface $link */
        $link = $element->link;
        $active_link = $link->getUrlObject()->toString();
        if (!str_starts_with($active_link, $current_path_alias['alias'])) {
          $output = [
            'active_link' => $active_link,
            'element' => $element,
          ];
        }
      }
    }
    return $output;
  }

}
