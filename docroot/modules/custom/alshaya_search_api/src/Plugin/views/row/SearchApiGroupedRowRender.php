<?php

namespace Drupal\alshaya_search_api\Plugin\views\row;

use Drupal\search_api\Plugin\views\row\SearchApiRow;

/**
 * Provides a row plugin for displaying a result as a grouped rendered item.
 *
 * @ViewsRow(
 *   id = "alshaya_search_api_grouped_row",
 *   title = @Translation("Grouped Rendered entity"),
 *   help = @Translation("Displays entity of the matching search API item along with groups"),
 * )
 *
 * @see search_api_views_plugins_row_alter()
 */
class SearchApiGroupedRowRender extends SearchApiRow {

  /**
   * {@inheritdoc}
   */
  public function render($row) {
    if (!empty($row->group_details)) {
      return $row->group_details;
    }
    return parent::render($row);
  }

}
