<?php

namespace Drupal\alshaya_addressbook\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\profile\Controller\ProfileController;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\profile\Entity\ProfileInterface;
use Drupal\user\UserInterface;
use Drupal\profile\Entity\ProfileTypeInterface;
use Drupal\Core\Link;

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
    if ($profile->bundle() != 'address_book') {
      return parent::setDefault($routeMatch);
    }

    $profile->setDefault(TRUE);
    $profile->save();

    drupal_set_message($this->t('Primary address is updated successfully.'));

    $url = $profile->urlInfo('collection');
    return $this->redirect($url->getRouteName(), $url->getRouteParameters(), $url->getOptions());
  }

  /**
   * Provides profile create form.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\user\UserInterface $user
   *   The user account.
   * @param \Drupal\profile\Entity\ProfileTypeInterface $profile_type
   *   The profile type entity for the profile.
   *
   * @return array
   *   Returns form array.
   */
  public function userProfileForm(RouteMatchInterface $route_match, UserInterface $user, ProfileTypeInterface $profile_type) {
    /** @var \Drupal\profile\Entity\ProfileType $profile_type */

    /** @var \Drupal\profile\Entity\ProfileInterface|bool $active_profile */
    $active_profile = $this->entityTypeManager()->getStorage('profile')->loadByUser($user, $profile_type->id());

    // If the profile type does not support multiple, only display an add form
    // if there are no entities, or an edit for the current.
    if (!$profile_type->getMultiple()) {

      // If there is an active profile, provide edit form.
      if ($active_profile) {
        return $this->editProfile($route_match, $user, $active_profile);
      }

      // Else show the add form.
      return $this->addProfile($route_match, $user, $profile_type);
    }
    // Display active, and link to create a profile.
    else {
      $build = [];

      // If there is no active profile, display add form.
      if (!$active_profile) {
        return $this->addProfile($route_match, $user, $profile_type);
      }

      $build['add_profile'] = Link::createFromRoute(
        $this->t('Add new @type', ['@type' => $profile_type->label()]),
        "alshaya_addressbook.add_address_ajax",
        [
          'user' => \Drupal::currentUser()->id(),
          'profile_type' => $profile_type->id(),
          'js' => 'nojs',
        ],
        [
          'attributes' => [
            'class' => ['use-ajax'],
            'rel' => 'address-book-form-wrapper',
          ],
        ])->toRenderable();

      $build['address_book_wrapper'] = [
        '#type' => 'item',
        '#markup' => '<div id="address-book-form-wrapper"></div>',
      ];

      // Render the active profiles.
      $build['active_profiles'] = [
        '#type' => 'view',
        '#name' => 'profiles',
        '#display_id' => 'profile_type_listing',
        '#arguments' => [$user->id(), $profile_type->id(), 1],
        '#embed' => TRUE,
        '#title' => $this->t('Active @type', ['@type' => $profile_type->label()]),
        '#pre_render' => [
          ['\Drupal\views\Element\View', 'preRenderViewElement'],
          'profile_views_add_title_pre_render',
        ],
      ];

      return $build;
    }
  }

  /**
   * Provides the profile submission form.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\user\UserInterface $user
   *   The user account.
   * @param \Drupal\profile\Entity\ProfileTypeInterface $profile_type
   *   The profile type entity for the profile.
   * @param bool $js
   *   The ajax value.
   *
   * @return array|\Drupal\Core\Ajax\AjaxResponse
   *   Return the form OR The ajax response object.
   */
  public function addAddress(RouteMatchInterface $route_match, UserInterface $user, ProfileTypeInterface $profile_type, $js = FALSE) {
    $form = parent::addProfile($route_match, $user, $profile_type);
    if ($js == 'nojs') {
      return $form;
    }
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#address-book-form-wrapper', $form));
    return $response;
  }

  /**
   * Provides the profile edit form.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\profile\Entity\ProfileInterface $profile
   *   The profile entity to edit.
   * @param bool $js
   *   The ajax value.
   *
   * @return array|\Drupal\Core\Ajax\AjaxResponse
   *   Return the form OR The ajax response object.
   */
  public function editAddress(RouteMatchInterface $route_match, ProfileInterface $profile, $js = FALSE) {
    $form = $this->entityFormBuilder()->getForm($profile, 'edit');
    if ($js == 'nojs') {
      return $form;
    }
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#address-book-form-wrapper', $form));
    return $response;
  }

}
