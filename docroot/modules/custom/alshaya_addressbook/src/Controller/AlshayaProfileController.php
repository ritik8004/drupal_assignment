<?php

namespace Drupal\alshaya_addressbook\Controller;

use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\profile\Entity\ProfileTypeInterface;
use Drupal\user\UserInterface;
use Drupal\profile\Controller\ProfileController;

/**
 * Returns responses for 'address_book' profile routes.
 */
class AlshayaProfileController extends ProfileController {

  /**
   * Overriding the ProfileController::userProfileForm().
   *
   * The /user/{user}/{profile_type} page renders the profile add form by
   * default. We don't want the 'profile add' form to be available on the page
   * and thus overriding here. It will only work for the 'address_book'
   * profile. For other profile types, behavior will be same as provided by the
   * profile module.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match service.
   * @param \Drupal\user\UserInterface $user
   *   Current user object.
   * @param \Drupal\profile\Entity\ProfileTypeInterface $profile_type
   *   Profile type entity.
   *
   * @return mixed
   *   Rendered array.
   */
  public function userProfileForm(RouteMatchInterface $route_match, UserInterface $user, ProfileTypeInterface $profile_type) {

    // Use default profile handling if profile is not of 'address_book' type.
    if ($profile_type->id() !== 'address_book') {
      return parent::userProfileForm($route_match, $user, $profile_type);
    }

    // Get user's First and Last name.
    $fname = $user->get('field_first_name') ? $user->get('field_first_name')->getValue()[0]['value'] : '';
    $lname = $user->get('field_last_name') ? $user->get('field_last_name')->getValue()[0]['value'] : '';

    $inline_template = '{% if fname is not empty %} {{ logged_in_as|t }} {% endif %} ';
    $inline_template .= '{% if fname is not empty %} {{ fname|t }} {% endif %} ';
    $inline_template .= '{% if lname is not empty %} {{ lname|t }} {% endif %}';

    // Render first and last name.
    $build['name'] = [
      '#type' => 'inline_template',
      '#template' => $inline_template,
      '#context' => [
        'logged_in_as' => 'Logged in as',
        'fname' => $fname,
        'lname' => $lname,
      ],
    ];

    $profile_empty = TRUE;

    $db = \Drupal::database();
    $profile_exists = $db->select('profile', 'pf')
      ->fields('pf', ['profile_id'])
      ->condition('uid', $user->id())
      ->condition('status', 1)
      ->condition('type', 'address_book')
      ->execute()->fetchField();

    // If at least one address exists for the user.
    if ($profile_exists) {
      $profile_empty = FALSE;
      // Render 'add' link.
      $build['add_profile'] = Link::createFromRoute(
        $this->t('Add new address'),
        "entity.profile.type.{$profile_type->id()}.user_profile_form.add",
        [
          'user' => \Drupal::currentUser()->id(),
          'profile_type' => $profile_type->id(),
          'destination' => 'user/' . \Drupal::currentUser()->id() . '/address_book',
        ]
      )
        ->toRenderable();
    }

    // If user has no address in address book.
    if ($profile_empty) {
      // Render empty message.
      $build['empty_message'] = [
        '#markup' => '<div class="addressbook-empty-message">' . $this->t('your address book is empty') . '</div>',
      ];
      // Render 'add' link.
      $build['add_profile'] = Link::createFromRoute(
        $this->t('Add new address'),
        "entity.profile.type.{$profile_type->id()}.user_profile_form.add",
        [
          'user' => \Drupal::currentUser()->id(),
          'profile_type' => $profile_type->id(),
          'destination' => 'user/' . \Drupal::currentUser()->id() . '/address_book',
        ]
      )
        ->toRenderable();
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function addPageTitle(ProfileTypeInterface $profile_type) {
    if ($profile_type !== 'address_book') {
      return parent::addPageTitle($profile_type);
    }

    return $this->t('address book');
  }

}
