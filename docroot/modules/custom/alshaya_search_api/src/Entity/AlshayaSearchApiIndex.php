<?php

namespace Drupal\alshaya_search_api\Entity;

use Drupal\search_api\Entity\Index;
use Drupal\search_api\Event\IndexingItemsEvent;
use Drupal\search_api\Event\ItemsIndexedEvent;
use Drupal\search_api\Event\SearchApiEvents;
use Drupal\search_api\SearchApiException;

/**
 * Class AlshayaSearchApiIndex.
 *
 * @package Drupal\alshaya_search_api\Entity
 */
class AlshayaSearchApiIndex extends Index {

  /**
   * {@inheritdoc}
   */
  public function indexSpecificItems(array $search_objects) {
    // Only difference from actual Entity class is that we do not invalidate
    // list cache here.
    if (!$search_objects || $this->read_only) {
      return [];
    }
    if (!$this->status) {
      $index_label = $this->label();
      throw new SearchApiException("Couldn't index values on index '$index_label' (index is disabled)");
    }

    /** @var \Drupal\search_api\Item\ItemInterface[] $items */
    $items = [];
    foreach ($search_objects as $item_id => $object) {
      $items[$item_id] = \Drupal::getContainer()
        ->get('search_api.fields_helper')
        ->createItemFromObject($this, $object, $item_id);
    }

    // Remember the items that were initially passed, to be able to determine
    // the items rejected by alter hooks and processors afterwards.
    $rejected_ids = array_keys($items);
    $rejected_ids = array_combine($rejected_ids, $rejected_ids);

    // Preprocess the indexed items.
    $this->alterIndexedItems($items);
    $description = 'This hook is deprecated in search_api 8.x-1.14 and will be removed in 9.x-1.0. Please use the "search_api.indexing_items" event instead. See https://www.drupal.org/node/3059866';
    \Drupal::moduleHandler()->alterDeprecated($description, 'search_api_index_items', $this, $items);
    $event = new IndexingItemsEvent($this, $items);
    \Drupal::getContainer()->get('event_dispatcher')->dispatch(SearchApiEvents::INDEXING_ITEMS, $event);
    foreach ($items as $item) {
      // This will cache the extracted fields so processors, etc., can retrieve
      // them directly.
      $item->getFields();
    }
    $this->preprocessIndexItems($items);

    // Remove all items still in $items from $rejected_ids. Thus, only the
    // rejected items' IDs are still contained in $ret, to later be returned
    // along with the successfully indexed ones.
    foreach ($items as $item_id => $item) {
      unset($rejected_ids[$item_id]);
    }

    // Items that are rejected should also be deleted from the server.
    if ($rejected_ids) {
      $this->getServerInstance()->deleteItems($this, $rejected_ids);
    }

    $indexed_ids = [];
    if ($items) {
      $indexed_ids = $this->getServerInstance()->indexItems($this, $items);
    }

    // Return the IDs of all items that were either successfully indexed or
    // rejected before being handed to the server.
    $processed_ids = array_merge(array_values($rejected_ids), array_values($indexed_ids));

    if ($processed_ids) {
      if ($this->hasValidTracker()) {
        $this->getTrackerInstance()->trackItemsIndexed($processed_ids);
      }
      // Since we've indexed items now, triggering reindexing would have some
      // effect again. Therefore, we reset the flag.
      $this->setHasReindexed(FALSE);

      $description = 'This hook is deprecated in search_api 8.x-1.14 and will be removed in 9.x-1.0. Please use the "search_api.items_indexed" event instead. See https://www.drupal.org/node/3059866';
      \Drupal::moduleHandler()->invokeAllDeprecated($description, 'search_api_items_indexed', [$this, $processed_ids]);

      /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
      $dispatcher = \Drupal::getContainer()->get('event_dispatcher');
      $dispatcher->dispatch(SearchApiEvents::ITEMS_INDEXED, new ItemsIndexedEvent($this, $processed_ids));
    }

    return $processed_ids;
  }

}
