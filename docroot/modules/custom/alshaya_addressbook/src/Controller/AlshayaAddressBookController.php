<?php

namespace Drupal\alshaya_addressbook\Controller;

use Drupal\alshaya_addressbook\AddressBookAreasTermsHelper;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\profile\Controller\ProfileController;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\profile\Entity\ProfileInterface;
use Drupal\user\UserInterface;
use Drupal\profile\Entity\ProfileTypeInterface;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * AlshayaAddressBookController class.
 */
class AlshayaAddressBookController extends ProfileController {

  /**
   * AddressBook Areas Terms helper service.
   *
   * @var \Drupal\alshaya_addressbook\AddressBookAreasTermsHelper
   */
  protected $areasTermsHelper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('datetime.time'),
      $container->get('alshaya_addressbook.area_terms_helper')
    );
  }

  /**
   * AlshayaAddressBookController constructor.
   *
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time.
   * @param \Drupal\alshaya_addressbook\AddressBookAreasTermsHelper $areas_terms_helper
   *   AddressBook Areas Terms helper service.
   */
  public function __construct(TimeInterface $time, AddressBookAreasTermsHelper $areas_terms_helper) {
    parent::__construct($time);
    $this->areasTermsHelper = $areas_terms_helper;
  }

  /**
   * AJAX callback to get list of areas for a governate.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   AJAX Response.
   */
  public function getAreasFromGovernate(Request $request) {
    $element = $request->request->get('_triggering_element_name');

    // Confirm it is a POST request and contains form data.
    if (empty($element)) {
      throw new NotFoundHttpException();
    }

    $request_params = $request->request->all();
    if (!is_array($request_params)) {
      throw new NotFoundHttpException();
    }

    // Get governate value dynamically to ensure it doesn't depend on form
    // structure.
    $governate = NestedArray::getValue($request_params, explode('[', str_replace(']', '', $element)));

    // Check if we have value available for governate.
    if (empty($governate)) {
      throw new NotFoundHttpException();
    }

    $areas = $this->areasTermsHelper->getAllAreasWithParent($governate);

    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand(NULL, 'updateAreaList', [$areas]));

    return $response;
  }

  /**
   * Mark profile as default.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match.
   * @param string $token
   *   CSRF Token.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect back to the currency listing.
   */
  public function setDefaultAddress(RouteMatchInterface $routeMatch, $token) {
    /* @var \Drupal\profile\Entity\Profile $profile */
    $profile = $routeMatch->getParameter('profile');

    /** @var \Drupal\Core\Access\CsrfTokenGenerator $csrf_token_generator */
    $csrf_token_generator = \Drupal::service('csrf_token');

    if (!$csrf_token_generator->validate($token, 'profile-' . $profile->id())) {
      throw new AccessDeniedHttpException();
    }

    /** @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager */
    $address_book_manager = \Drupal::service('alshaya_addressbook.manager');

    // If not address book, use default handling.
    if ($profile->bundle() != 'address_book') {
      return parent::setDefault($routeMatch);
    }

    $profile->setDefault(TRUE);

    // We have to save here first to ensure other addresses that are default
    // are demoted.
    $profile->save();

    if ($address_book_manager->pushUserAddressToApi($profile)) {
      drupal_set_message($this->t('Primary address is updated successfully.'));
    }

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
    // Custom check, we don't provide address book functionality for admins.
    if (!alshaya_acm_customer_is_customer($user)) {
      throw new AccessDeniedHttpException();
    }

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
        '#markup' => '<div id="address-book-form-wrapper" class="address-book-form-ajax-wrapper"></div>',
      ];

      // Render the active profiles.
      $build['active_profiles'] = [
        '#type' => 'view',
        '#name' => 'address_book',
        '#display_id' => 'address_book',
        '#arguments' => [$user->id()],
        '#embed' => TRUE,
        '#title' => $this->t('Active @type', ['@type' => $profile_type->label()]),
        '#pre_render' => [
          ['\Drupal\views\Element\View', 'preRenderViewElement'],
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
    $response->addCommand(new HtmlCommand('#address-book-form-wrapper', $form));
    $response->addCommand(new RemoveCommand('.messages__wrapper'));
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
    $response->addCommand(new HtmlCommand('#address-book-form-wrapper', $form));
    $response->addCommand(new RemoveCommand('.messages__wrapper'));
    $response->addCommand(new InvokeCommand(NULL, 'correctFloorFieldLabel', []));
    return $response;
  }

}
