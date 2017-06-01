<?php

namespace Drupal\alshaya_product\Plugin\views\access;

use Drupal\Core\Session\AccountInterface;
use Drupal\views\Plugin\views\access\AccessPluginBase;
use Symfony\Component\Routing\Route;

/**
 * Access plugin that provides taxonomy_term_plp access control.
 *
 * @ingroup views_access_plugins
 *
 * @ViewsAccess(
 *   id = "taxonomy_term_plp",
 *   title = @Translation("Taxonomy Term - PLP"),
 *   help = @Translation("Check acess content for all except PLP.")
 * )
 */
class TaxonomyTermPlp extends AccessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function summaryTitle() {
    return $this->t('Taxonomy Terms Content - Except PLP');
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    if (\Drupal::routeMatch()->getRouteName() == 'entity.taxonomy_term.canonical') {
      // Get the term from args.
      /** @var \Drupal\taxonomy\Entity\Term $term */
      $term = \Drupal::request()->attributes->get('taxonomy_term');

      // Disable access to main block for product category page.
      if ($term->getVocabularyId() == 'acq_product_category') {
        return FALSE;
      }
    }

    return $account->hasPermission('access content');
  }

  /**
   * {@inheritdoc}
   */
  public function alterRouteDefinition(Route $route) {
    $route->setRequirement('_access', 'TRUE');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['route'];
  }

}
