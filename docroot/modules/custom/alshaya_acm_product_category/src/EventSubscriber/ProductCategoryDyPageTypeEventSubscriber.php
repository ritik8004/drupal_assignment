<?php

namespace Drupal\alshaya_acm_product_category\EventSubscriber;

use Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Product Category DyPage Type EventSubscriber.
 *
 * @package Drupal\alshaya_acm_product_category\EventSubscriber
 */
class ProductCategoryDyPageTypeEventSubscriber implements EventSubscriberInterface {

  /**
   * Product Category Tree from route.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface
   */
  protected $categoryTree;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Entity Repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * ProductCategoryDyPageTypeEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface $categoryTree
   *   Category tree object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   Entity Repository.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route Match Object.
   */
  public function __construct(
    ProductCategoryTreeInterface $categoryTree,
    EntityTypeManagerInterface $entityTypeManager,
    EntityRepositoryInterface $entityRepository,
    RouteMatchInterface $route_match
  ) {
    $this->categoryTree = $categoryTree;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityRepository = $entityRepository;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events['dy.set.context'][] = ['setContextCategory', 200];
    return $events;
  }

  /**
   * Set CATEGORY Context for Dynamic yield script.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   Dispatched Event.
   */
  public function setContextCategory(Event $event) {
    $term = $this->routeMatch->getParameter('taxonomy_term');
    if ($term instanceof TermInterface && $term->bundle() === 'rcs_category') {
      // We only have PLP `type`, don't have `data` for V3. It is handled in
      // 'alshaya_rcs_listing_dy.js' file.
      $event->setDyContext('CATEGORY');
      return;
    }
    $term = $this->categoryTree->getCategoryTermFromRoute();
    if ($term) {
      $event->setDyContext('CATEGORY');
      $ancestors = $this->entityTypeManager->getStorage('taxonomy_term')->loadAllParents($term->id());
      $data = [];
      foreach ($ancestors as $ancestor) {
        // Only use english terms for Dynamic yield context.
        $english_term = $this->entityRepository->getTranslationFromContext($ancestor, 'en');
        $data[] = $english_term->getName();
      }
      $data = array_reverse($data);
      $event->setDyContextData($data);
    }
  }

}
