<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;

/**
 * Provides the delivery home pane for members.
 *
 * @ACQCheckoutPane(
 *   id = "member_delivery_home",
 *   label = @Translation("Home delivery"),
 *   defaultStep = "delivery",
 *   wrapperElement = "fieldset",
 * )
 */
class MemberDeliveryHome extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    return \Drupal::currentUser()->isAuthenticated();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'weight' => 1,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $account = \Drupal::currentUser();

    // This class is required to make theme work properly.
    $pane_form['#attributes']['class'] = 'c-address-book';

    $pane_form['address_book_wrapper'] = [
      '#type' => 'item',
      '#markup' => '<div id="address-book-form-wrapper"></div>',
    ];

    $pane_form['header'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['addresses_header'],
      ],
    ];

    $pane_form['header']['title'] = [
      '#type' => 'markup',
      '#markup' => '<h2>' . $this->t('choose delivery address') . '</h2>',
    ];

    $add_profile_route_params = [
      'user' => \Drupal::currentUser()->id(),
      'profile_type' => 'address_book',
      'js' => 'nojs',
    ];

    $add_profile_route_options = [
      'attributes' => [
        'class' => ['use-ajax'],
        'rel' => 'address-book-form-wrapper',
      ],
      'query' => [
        'from' => 'checkout',
      ],
    ];

    $pane_form['header']['add_profile'] = Link::createFromRoute(
      $this->t('add new address'),
      'alshaya_addressbook.add_address_ajax',
      $add_profile_route_params,
      $add_profile_route_options)->toRenderable();

    $pane_form['addresses'] = [
      '#type' => 'view',
      '#name' => 'address_book',
      '#display_id' => 'address_book',
      '#embed' => TRUE,
      '#title' => '',
      '#pre_render' => [
        ['\Drupal\views\Element\View', 'preRenderViewElement'],
      ],
    ];

    return $pane_form;
  }

}
