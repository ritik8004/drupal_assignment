<?php

namespace Drupal\alshaya_search_api\Plugin\views\row;

use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\search_api\SearchApiException;
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

    $datasource_id = $row->search_api_datasource;

    if (!($row->_object instanceof ComplexDataInterface)) {
      $context = [
        '%item_id' => $row->search_api_id,
        '%view' => $this->view->storage->label(),
      ];
      $this->getLogger()->warning('Failed to load item %item_id in view %view.', $context);
      return $row;
    }

    if (!$this->index->isValidDatasource($datasource_id)) {
      $context = [
        '%datasource' => $datasource_id,
        '%view' => $this->view->storage->label(),
      ];
      $this->getLogger()->warning('Item of unknown datasource %datasource returned in view %view.', $context);
      return '';
    }
    // Always use the default view mode if it was not set explicitly in the
    // options.
    $view_mode = 'default';
    $bundle = $this->index->getDatasource($datasource_id)->getItemBundle($row->_object);
    if (isset($this->options['view_modes'][$datasource_id][$bundle])) {
      $view_mode = $this->options['view_modes'][$datasource_id][$bundle];
    }

    try {
      return $this->index->getDatasource($datasource_id)->viewItem($row->_object, $view_mode);
    }
    catch (SearchApiException $e) {
      $this->logException($e);
      return '';
    }
  }

}
