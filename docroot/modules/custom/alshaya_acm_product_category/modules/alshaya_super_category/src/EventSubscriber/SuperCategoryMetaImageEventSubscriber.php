<?php

namespace Drupal\alshaya_super_category\EventSubscriber;

use Drupal\alshaya_seo\Event\MetaImageRenderEvent;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;

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
   * DefaultMetaImageEventSubscriber constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route Match Object.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
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
    $product_category_tree = \Drupal::service('alshaya_acm_product_category.product_category_tree');
    $term = $product_category_tree->getCategoryTermFromRoute();
    if (empty($term)) {
      return;
    }
    $parents = $product_category_tree->getCategoryTermParents($term);
    if (!empty($parents)) {
      $parent = end($parents);
    }
    if (!$parent instanceof TermInterface) {
      return;
    }
    // Create a name without spaces and any special character.
    $term_clean_name = Html::cleanCssIdentifier(Unicode::strtolower($parent->label()));
    // Set the language suffix for logo based on current language.
    $langcode = \Drupal::service('language_manager')
      ->getCurrentLanguage()
      ->getId();

    $langcode = ($langcode != 'en') ? '-' . $langcode : '';
    // Current active theme object.
    $theme = \Drupal::service('theme.manager')->getActiveTheme();
    // Set the logo path based on term name and current language.
    $logo_path = '/' . $theme->getPath() . '/imgs/logos/' . $term_clean_name . '-logo';
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
