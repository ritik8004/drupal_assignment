<?php

/**
 * @file
 * Tokens related functions for the alshaya_facets_pretty_path.
 */

use Drupal\Core\Render\BubbleableMetadata;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Drupal\alshaya_facets_pretty_paths\AlshayaFacetsPrettyPathsHelper;

/**
 * Implements hook_tokens_alter().
 *
 * [alshaya_seo:term_name] is the token used for PLP page meta title.
 * However, for PLP pages with facets, we replace the url with the
 * facet value selected by the user.
 */
function alshaya_facets_pretty_paths_tokens_alter(array &$replacements, array $context, BubbleableMetadata $bubbleable_metadata) {
  /** @var \Drupal\alshaya_facets_pretty_paths\AlshayaFacetsPrettyPathsHelper $facets_pretty_path_helper */
  $facets_pretty_path_helper = \Drupal::service('alshaya_facets_pretty_paths.pretty_paths_helper');

  if ($context['type'] == 'alshaya_seo' && $facets_pretty_path_helper->isPrettyPathEnabled('plp')) {
    if (isset($context['tokens']['term_name'])) {
      // Getting the active facet items.
      $facet_summary_items = $facets_pretty_path_helper->getFacetSummaryItems(AlshayaFacetsPrettyPathsHelper::VISIBLE_IN_META_TITLE, 'plp');
      $first_word = strtok($replacements[$context['tokens']['term_name']], ' ');
      $meta_title_prefix = preg_replace('/' . $first_word . '/', implode(' ', array_filter($facet_summary_items[0])), $replacements[$context['tokens']['term_name']], 1);
      $meta_title_suffix = implode(' ', $facet_summary_items[1]);
      // Concaniting first word(Shop)
      // $meta_title_prefix (green)
      // Shop black Mini Dresses Collection for Ladies Online.
      $replacements[$context['tokens']['term_name']] = implode(' ', array_filter([
        $first_word,
        $meta_title_prefix,
        $meta_title_suffix,
      ]));
    }

    if (isset($context['tokens']['desc_start'])) {
      $request = \Drupal::request();
      $title = '';
      $facet_summary_items = \Drupal::service('alshaya_facets_pretty_paths.pretty_paths_helper')->getFacetSummaryItems(AlshayaFacetsPrettyPathsHelper::VISIBLE_IN_META_DESCRIPTION, 'plp');
      // Getting the page title. (Mini Dresses)
      if ($route = $request->attributes->get(RouteObjectInterface::ROUTE_OBJECT)) {
        $title_object = \Drupal::service('title_resolver')->getTitle($request, $route);
        $title = $title_object['#markup'];
      }
      // Replacing page title by adding facets
      // (Shop exclusive and luxury H&M green Mini Dresses for Ladies).
      $meta_title_prefix = preg_replace('/' . $title . '/', implode(' ', array_filter($facet_summary_items[0])) . ' ' . $title, $replacements[$context['tokens']['desc_start']], 1);
      $meta_title_suffix = implode(' ', $facet_summary_items[1]);
      $replacements[$context['tokens']['desc_start']] = implode(' ', array_filter([
        $meta_title_prefix,
        $meta_title_suffix,
      ]));
    }
  }
}
