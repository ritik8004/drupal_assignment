<?php

namespace Drupal\alshaya_custom;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;

/**
 * Utilty Class.
 */
class Utility {

  /**
   * Theme Handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Entity Type Manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Utility constructor.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   Theme Handler service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct(ThemeHandlerInterface $theme_handler, EntityTypeManagerInterface $entity_type_manager) {
    $this->themeHandler = $theme_handler;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Get theme name by alshaya_theme_type theme key.
   *
   * Get the theme name for the key 'alshaya_theme_type' set in the themes
   * *.info.yml file. $skip_themes variable checks if we need to skip few themes
   * or not as at the point of installation, installed themed is also available.
   *
   * @param string $theme_type
   *   Theme type.
   * @param array $skip_themes
   *   Themes for which need to skip value.
   *
   * @return mixed|string
   *   Theme name.
   */
  public function getThemeByThemeType($theme_type = '', array $skip_themes = []) {
    if (!empty($theme_type)) {
      foreach ($this->themeHandler->listInfo() as $theme) {
        // If the key matches the expected type and the theme has not been
        // flagged to be ignored.
        if (!empty($theme->info['alshaya_theme_type'])
          && $theme->info['alshaya_theme_type'] == $theme_type
          && !in_array($theme->getName(), $skip_themes)
          && !empty($this->entityTypeManager->getStorage('block')->loadByProperties(['theme' => $theme->getName()]))
        ) {
          return $theme->getName();
        }
      }
    }

    return NULL;
  }

}
