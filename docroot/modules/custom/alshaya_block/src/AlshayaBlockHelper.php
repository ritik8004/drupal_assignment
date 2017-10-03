<?php

namespace Drupal\alshaya_block;

use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Menu\MenuLinkTree;
use Drupal\Core\Path\AliasStorage;
use Drupal\Core\Path\CurrentPathStack;

/**
 * Helper class to check if the current path belongs to main menu.
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
   * @var \Drupal\Core\Path\AliasStorage
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
   * @param \Drupal\Core\Path\AliasStorage $alias_storage
   *   The alias storage service.
   */
  public function __construct(LanguageManager $language_manager, MenuLinkTree $menu_tree, CurrentPathStack $current_path, AliasStorage $alias_storage) {
    $this->languageManager = $language_manager;
    $this->menuTree = $menu_tree;
    $this->currentPath = $current_path;
    $this->aliasStorage = $alias_storage;
  }

  /**
   * Check if the current path belongs to main menu.
   */
  public function checkCurrentPathInMainMenu() {
    $output = [];
    // Get current language code.
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    // @todo: Make the menu name "main" dynamic.
    // Get the main menu tree to get the current active path.
    $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters('main');
    $parameters->setTopLevelOnly();
    $tree = $this->menuTree->load('main', $parameters);

    // Retrieve an array which contains the path pieces.
    $current_path = $this->currentPath->getPath();
    // Get current path alias.
    $current_path_alias = $this->aliasStorage->load(['source' => $current_path, 'langcode' => $langcode]);

    // Get the active link if any!.
    foreach ($tree as $key => $element) {
      if ($element->inActiveTrail) {
        // @var $link \Drupal\Core\Menu\MenuLinkInterface
        $link = $element->link;
        $active_link = $link->getUrlObject()->toString();
        if (strpos($active_link, $current_path_alias['alias']) !== 0) {
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
