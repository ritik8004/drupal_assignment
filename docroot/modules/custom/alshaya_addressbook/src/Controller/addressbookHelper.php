<?php

namespace Drupal\alshaya_addressbook\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;

/**
 * Controller contains all general helper functions.
 */
class AddressBookHelper {

  /**
   * Close modal window.
   */
  public function closeModal() {
    $response = new AjaxResponse();
    $response->addCommand(new CloseModalDialogCommand());
    return $response;
  }

}
