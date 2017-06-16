<?php

namespace Drupal\alshaya_acm_product\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;

/**
 * Class AlshayaPDPBreadcrumbBuilder.
 */
class AlshayaPDPBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    // Breadcrumb for 'pdp' pages.
    if ($route_match->getRouteName() == 'entity.node.canonical') {
      return $route_match->getParameter('node')->bundle() == 'acq_product';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute(t('Home'), '<front>'));
    /* @var \Drupal\node\Entity\Node $node */
    $node = $route_match->getParameter('node');
    if ($field_category = $node->get('field_category')) {
      $term_list = $field_category->getValue();
      $inner_term = $this->termTreeGroup($term_list);
      if ($inner_term) {
        $parents = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadAllParents($inner_term);
        foreach (array_reverse($parents) as $term) {
          $term = \Drupal::service('entity.repository')->getTranslationFromContext($term);
          $breadcrumb->addCacheableDependency($term);
          $breadcrumb->addLink(Link::createFromRoute($term->getName(), 'entity.taxonomy_term.canonical', ['taxonomy_term' => $term->id()]));
        }
        // This breadcrumb builder is based on a route parameter, and hence it
        // depends on the 'route' cache context.
        $breadcrumb->addCacheContexts(['route']);
      }
    }

    $request = \Drupal::request();
    $title = \Drupal::service('title_resolver')->getTitle($request, $route_match->getRouteObject());
    $breadcrumb->addLink(Link::createFromRoute($title, 'entity.node.canonical', ['node' => $node->id()]));
    // Cacheability data of the node.
    $breadcrumb->addCacheTags(['node:' . $node->id()]);

    return $breadcrumb;
  }

  /**
   * Get most inner term for the first group.
   *
   * @param array $terms
   *   Terms array.
   *
   * @return int
   *   Term id.
   */
  protected function termTreeGroup(array $terms = []) {
    if (!empty($terms)) {
      $root_group = $this->getRootGroup($terms[0]['target_id']);
      $root_group_terms = [];
      foreach ($terms as $term) {
        $root = $this->getRootGroup($term['target_id']);
        if ($root == $root_group) {
          $root_group_terms[] = $term['target_id'];
        }
      }

      return $this->getInnerDepthTerm($root_group_terms);
    }

    return NULL;

  }

  /**
   * Get the root level parent tid of a given term.
   *
   * @param int $tid
   *   Term id.
   *
   * @return int
   *   Root parent term id.
   */
  protected function getRootGroup($tid) {
    $db = \Drupal::database();
    // Recursive call to get parent root parent tid.
    while ($tid > 0) {
      $query = $db->select('taxonomy_term_hierarchy', 'tth');
      $query->fields('tth', ['parent']);
      $query->condition('tth.tid', $tid);
      $parent = $query->execute()->fetchField();
      if ($parent == 0) {
        return $tid;
      }

      $tid = $parent;
    }
  }

  /**
   * Get the most inner term term based on the depth.
   *
   * @param array $terms
   *   Array of term ids.
   *
   * @return int
   *   The term id.
   */
  protected function getInnerDepthTerm(array $terms = []) {
    $db = \Drupal::database();
    $current_langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $depths = $db->select('taxonomy_term_field_data', 'ttfd')
      ->fields('ttfd', ['tid', 'depth_level'])
      ->condition('ttfd.tid', $terms, 'IN')
      ->condition('ttfd.langcode', $current_langcode)
      ->execute()->fetchAllKeyed();

    // Flip key/value.
    $terms = array_flip($terms);
    // Merge two array (overriding depth value).
    $depths = array_replace($terms, $depths);
    // Get all max values and get first one.
    $max_depth = array_keys($depths, max($depths));
    $most_inner_tid = $max_depth[0];

    return $most_inner_tid;
  }

}
