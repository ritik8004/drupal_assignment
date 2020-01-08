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
  if ($context['type'] == 'alshaya_seo') {
    if (isset($context['tokens']['term_name'])) {
      $facet_summary_items = \Drupal::service('alshaya_facets_pretty_paths.pretty_paths_helper')->getFacetSummaryItems(AlshayaFacetsPrettyPathsHelper::VISIBLE_IN_META_TITLE);
      $first_word = strtok($replacements[$context['tokens']['term_name']], ' ');
      $meta_title_prefix = preg_replace('/' . $first_word . '/', implode(' ', array_filter($facet_summary_items[0])), $replacements[$context['tokens']['term_name']], 1);
      $meta_title_suffix = implode(' ', $facet_summary_items[1]);
      $replacements[$context['tokens']['term_name']] = implode(' ', array_filter([
        $first_word,
        $meta_title_prefix,
        $meta_title_suffix,
      ]));
    }
    if (isset($context['tokens']['desc_start'])) {
      $request = \Drupal::request();
      $title = '';
      $facet_summary_items = \Drupal::service('alshaya_facets_pretty_paths.pretty_paths_helper')->getFacetSummaryItems(AlshayaFacetsPrettyPathsHelper::VISIBLE_IN_META_DESCRIPTION);
      if ($route = $request->attributes->get(RouteObjectInterface::ROUTE_OBJECT)) {
        $title_object = \Drupal::service('title_resolver')->getTitle($request, $route);
        $title = $title_object['#markup'];
      }
      // Replacing page title with the new value.
      $meta_title_prefix = preg_replace('/' . $title . '/', implode(' ', array_filter($facet_summary_items[0])) . ' ' . $title, $replacements[$context['tokens']['desc_start']], 1);
      $meta_title_suffix = implode(' ', $facet_summary_items[1]);
      $replacements[$context['tokens']['desc_start']] = implode(' ', array_filter([
        $meta_title_prefix,
        $meta_title_suffix,
      ]));
    }
  }
}
