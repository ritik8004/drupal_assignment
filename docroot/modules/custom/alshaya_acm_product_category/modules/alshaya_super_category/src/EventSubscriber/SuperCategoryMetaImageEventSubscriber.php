<?php

namespace Drupal\alshaya_super_category\EventSubscriber;

use Drupal\alshaya_seo\Event\MetaImageRenderEvent;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Theme\ThemeManagerInterface;

/**
 * Class SuperCtegoryMetaImageEventSubscriber.
 *
 * @package Drupal\alshaya_super_category\EventSubscriber
 */
class SuperCategoryMetaImageEventSubscriber implements EventSubscriberInterface {

  /**
   * Route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Product category tree manager.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  private $productCategoryTree;

  /**
   * Theme Manager service.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * DefaultMetaImageEventSubscriber constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route Match Object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $productCategoryTree
   *   Product category tree manager.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   Theme Manager.
   */
  public function __construct(RouteMatchInterface $route_match, LanguageManagerInterface $language_manager, ProductCategoryTree $productCategoryTree, ThemeManagerInterface $theme_manager) {
    $this->routeMatch = $route_match;
    $this->languageManager = $language_manager;
    $this->productCategoryTree = $productCategoryTree;
    $this->themeManager = $theme_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[MetaImageRenderEvent::EVENT_NAME][] = ['setSuperCategoryLogoMetaImage', 300];
    return $events;
  }

  /**
   * Subscriber Callback for the event.
   *
   * @param \Drupal\alshaya_seo\MetaImageRenderEvent $event
   *   The dispatch event.
   */
  public function setSuperCategoryLogoMetaImage(MetaImageRenderEvent $event) {
    $term = $this->productCategoryTree->getCategoryTermFromRoute();
    if (empty($term)) {
      return;
    }
    $parents = $this->productCategoryTree->getCategoryTermParents($term);
    if (!empty($parents)) {
      $parent = end($parents);
    }
    if (!$parent instanceof TermInterface) {
      return;
    }
    // Create a name without spaces and any special character.
    $term_clean_name = Html::cleanCssIdentifier(Unicode::strtolower($parent->label()));
    // Set the language suffix for logo based on current language.
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    $langcode = ($langcode != 'en') ? '-' . $langcode : '';
    // Current active theme object.
    // Set the logo path based on term name and current language.
    $logo_path = '/' . $this->themeManager->getActiveTheme()->getPath() . '/imgs/logos/' . $term_clean_name . '-logo';
    $logo_relative_path = DRUPAL_ROOT . $logo_path;
    // Check logo fallback.
    $logo = '';
    if (file_exists($logo_relative_path . $langcode . '.svg')) {
      $logo = $logo_path . $langcode . '.svg';
    }
    elseif (file_exists($logo_relative_path . '.svg')) {
      $logo = $logo_path . '.svg';
    }
    if ($logo) {
      $event->setMetaImage($logo);
    }
  }

}
