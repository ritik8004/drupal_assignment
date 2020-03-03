<?php

namespace Drupal\alshaya_super_category\EventSubscriber;

use Drupal\alshaya_seo\Event\MetaImageRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class SuperCtegoryMetaImageEventSubscriber.
 *
 * @package Drupal\alshaya_super_category\EventSubscriber
 */
class SuperCategoryMetaImageEventSubscriber implements EventSubscriberInterface {

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
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * DefaultMetaImageEventSubscriber constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $productCategoryTree
   *   Product category tree manager.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   Theme Manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   */
  public function __construct(LanguageManagerInterface $language_manager, ProductCategoryTree $productCategoryTree, ThemeManagerInterface $theme_manager, ConfigFactoryInterface $config_factory) {
    $this->languageManager = $language_manager;
    $this->productCategoryTree = $productCategoryTree;
    $this->themeManager = $theme_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[MetaImageRenderEvent::EVENT_NAME][] = ['setSuperCategoryLogoMetaImage', 150];
    return $events;
  }

  /**
   * Subscriber Callback for the event.
   *
   * @param \Drupal\alshaya_seo\MetaImageRenderEvent $event
   *   The dispatch event.
   */
  public function setSuperCategoryLogoMetaImage(MetaImageRenderEvent $event) {
    if (empty($this->configFactory->get('alshaya_super_category.settings')->get('status'))) {
      return;
    }

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

    // Set the language suffix for logo based on current language.
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    // Create a name without spaces and any special character.
    $parent_lang = \Drupal::service('entity.repository')->getTranslationFromContext($parent, $langcode);
    $term_clean_name = Html::cleanCssIdentifier(Unicode::strtolower($parent_lang->label()));

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
