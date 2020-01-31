<?php

namespace Drupal\alshaya_acm_product_category\EventSubscriber;

use Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductCategoryDyPageTypeEventSubscriber.
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
   * ProductCategoryDyPageTypeEventSubscriber constructor.
   *
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface $categoryTree
   *   Category tree object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   Entity Repository.
   */
  public function __construct(
    ProductCategoryTreeInterface $categoryTree,
    EntityTypeManagerInterface $entityTypeManager,
    EntityRepositoryInterface $entityRepository
  ) {
    $this->categoryTree = $categoryTree;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityRepository = $entityRepository;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
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
