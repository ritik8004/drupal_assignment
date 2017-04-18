<?php

namespace Drupal\alshaya_addressbook\Controller;

use Drupal\profile\Controller\ProfileController;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * AlshayaAddressBookController class.
 */
class AlshayaAddressBookController extends ProfileController {

  /**
   * Mark profile as default.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect back to the currency listing.
   */
  public function setDefault(RouteMatchInterface $routeMatch) {
    /* @var \Drupal\profile\Entity\Profile $profile */
    $profile = $routeMatch->getParameter('profile');

    // If not address book, use default handling.
    if ($profile->getType() != 'address_book') {
      return parent::setDefault($routeMatch);
    }

    $profile->setDefault(TRUE);
    $profile->save();

    drupal_set_message($this->t('Primary address is updated successfully.'));

    $url = $profile->urlInfo('collection');
    return $this->redirect($url->getRouteName(), $url->getRouteParameters(), $url->getOptions());
  }

}
