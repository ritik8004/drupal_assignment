<?php

namespace Drupal\alshaya_click_collect\Ajax;

use Drupal\Core\Ajax\BaseCommand;

/**
 * AJAX command to render store list view and map view.
 */
class ClickCollectStoresCommand extends BaseCommand {

  /**
   * Constructs a EntitySaveCommand object.
   *
   * @param string $data
   *   The data to pass on to the client side.
   */
  public function __construct($data) {
    parent::__construct('clickCollectStoresView', $data);
  }

}
