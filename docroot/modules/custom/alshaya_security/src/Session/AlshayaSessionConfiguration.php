<?php

namespace Drupal\alshaya_security\Session;

use Drupal\Core\Session\SessionConfiguration;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines the default session configuration generator.
 */
class AlshayaSessionConfiguration extends SessionConfiguration {

  /**
   * {@inheritdoc}
   */
  public function getOptions(Request $request) {
    $options = parent::getOptions($request);

    // Cybersource works with a page on their domain sending POST request to
    // our domain but latest versions of browsers don't add our session cookie
    // in this case.
    // We need to use samesite=None in this case but PHP 7.2 functions and
    // configurations don't support it. So we alter the domain configuration to
    // pass the samesite param as well.
    $options['cookie_domain'] .= '; samesite=None';

    return $options;
  }

}
