<?php

namespace Drupal\alshaya_search_api\Plugin\views\row;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\search_api\LoggerTrait;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\search_api\Plugin\views\query\SearchApiQuery;
use Drupal\search_api\SearchApiException;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\row\RowPluginBase;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
class SearchApiGroupedRowRender extends RowPluginBase {

  use LoggerTrait;

  /**
   * The search index.
   *
   * @var \Drupal\search_api\IndexInterface
   */
  protected $index;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $row */
    $row = parent::create($container, $configuration, $plugin_id, $plugin_definition);

    $row->setEntityTypeManager($container->get('entity_type.manager'));
    $row->setLogger($container->get('logger.channel.search_api'));

    return $row;
  }

  /**
   * Retrieves the entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager.
   */
  public function getEntityTypeManager() {
    return $this->entityTypeManager ?: \Drupal::entityTypeManager();
  }

  /**
   * Sets the entity type manager.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The new entity type manager.
   *
   * @return $this
   */
  public function setEntityTypeManager(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $base_table = $view->storage->get('base_table');
    $this->index = SearchApiQuery::getIndexFromTable($base_table, $this->getEntityTypeManager());
    if (!$this->index) {
      $view_label = $view->storage->label();
      throw new \InvalidArgumentException("View '$view_label' is not based on Search API but tries to use its row plugin.");
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['view_modes'] = ['default' => []];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    /** @var \Drupal\search_api\Datasource\DatasourceInterface $datasource */
    foreach ($this->index->getDatasources() as $datasource_id => $datasource) {
      $datasource_label = $datasource->label();
      if (!$datasource->getViewModes()) {
        $form['view_modes'][$datasource_id] = [
          '#type' => 'item',
          '#title' => $this->t('Default View mode for datasource %name', ['%name' => $datasource_label]),
          '#description' => $this->t("This datasource doesn't have any view modes available. It is therefore not possible to display results of this datasource using this row plugin."),
        ];
        continue;
      }

      foreach ($datasource->getBundles() as $bundle_id => $bundle_label) {
        $title = $this->t('View mode for datasource %datasource, bundle %bundle', ['%datasource' => $datasource_label, '%bundle' => $bundle_label]);
        $view_modes = $datasource->getViewModes($bundle_id);
        if (!$view_modes) {
          $form['view_modes'][$datasource_id][$bundle_id] = [
            '#type' => 'item',
            '#title' => $title,
            '#description' => $this->t("This bundle doesn't have any view modes available. It is therefore not possible to display results of this bundle using this row plugin."),
          ];
          continue;
        }
        $form['view_modes'][$datasource_id][$bundle_id] = [
          '#type' => 'select',
          '#options' => $view_modes,
          '#title' => $title,
          '#default_value' => key($view_modes),
        ];
        if (isset($this->options['view_modes'][$datasource_id][$bundle_id])) {
          $form['view_modes'][$datasource_id][$bundle_id]['#default_value'] = $this->options['view_modes'][$datasource_id][$bundle_id];
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preRender($result) {
    // Load all result objects at once, before rendering.
    $items_to_load = [];
    foreach ($result as $i => $row) {
      if (empty($row->_object)) {
        $items_to_load[$i] = $row->search_api_id;
      }
    }

    $items = $this->index->loadItemsMultiple($items_to_load);
    foreach ($items_to_load as $i => $item_id) {
      if (isset($items[$item_id])) {
        $result[$i]->_object = $items[$item_id];
        $result[$i]->_item->setOriginalObject($items[$item_id]);
      }
    }
  }

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

  /**
   * {@inheritdoc}
   */
  public function query() {}

}
