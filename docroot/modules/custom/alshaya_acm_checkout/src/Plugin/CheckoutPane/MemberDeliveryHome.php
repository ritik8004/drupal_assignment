<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\alshaya_acm_checkout\CheckoutDeliveryMethodTrait;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\profile\Entity\Profile;

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
  // Add trait to get selected delivery method tab.
  use CheckoutDeliveryMethodTrait;

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    return \Drupal::currentUser()->isAuthenticated() && alshaya_acm_customer_is_customer(\Drupal::currentUser());
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
    if ($this->getSelectedDeliveryMethod() != 'hd') {
      return $pane_form;
    }

    // Check if user is changing his mind, if so clear shipping info.
    if ($this->isUserChangingHisMind()) {
      $this->clearShippingInfo();
    }

    $pane_form['#attributes']['class'][] = 'active--tab--content';

    // This class is required to make theme work properly.
    $pane_form['#attributes']['class'][] = 'c-address-book';

    $pane_form['#suffix'] = '<div class="fieldsets-separator">' . $this->t('OR') . '</div>';

    $cart = $this->getCart();
    $address_info = $this->getAddressInfo('hd');

    $pane_form['address_form'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'address-book-form-wrapper',
        'class' => ['hidden'],
      ],
      '#attached' => [
        'library' => [
          'core/drupal.form',
          'alshaya_white_label/convert_to_select2',
          'clientside_validation_jquery/cv.jquery.validate',
        ],
      ],
    ];

    $pane_form['address_form']['title'] = [
      '#markup' => '<div class="title delivery-address-form-title">' . $this->t('add new address') . '</div>',
    ];

    $pane_form['address_form']['address_id'] = [
      '#type' => 'hidden',
      '#default_value' => '',
      '#attributes' => [
        'id' => 'address-form-address-id',
      ],
    ];

    $pane_form['address_form']['form'] = [
      '#type' => 'address',
      '#title' => '',
      '#default_value' => ['country_code' => _alshaya_custom_get_site_level_country_code()],
    ];

    $pane_form['address_form']['save'] = [
      '#name' => 'save_address',
      '#type' => 'button',
      '#value' => $this->t('deliver to this address'),
      '#executes_submit_callback' => FALSE,
      '#ajax' => [
        'callback' => [$this, 'saveAddressAjaxCallback'],
      ],
    ];

    $pane_form['address_form']['cancel'] = [
      '#type' => 'button',
      '#value' => $this->t('cancel'),
      '#executes_submit_callback' => FALSE,
      '#attributes' => [
        'id' => 'cancel-address-add-edit',
      ],
      '#limit_validation_errors' => [],
    ];

    $pane_form['header'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['addresses-header'],
      ],
    ];

    $pane_form['header']['title'] = [
      '#markup' => '<div class="title delivery-address-title">' . $this->t('choose delivery address') . '</div>',
    ];

    $pane_form['header']['add_profile'] = [
      '#type' => 'button',
      '#value' => $this->t('add new address'),
      '#executes_submit_callback' => FALSE,
      '#attributes' => [
        'id' => 'add-address-button',
      ],
      '#limit_validation_errors' => [],
    ];

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

    $shipping_methods = [];
    $default_shipping = NULL;

    $pane_form['address']['selected'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['selected-address-wrapper'],
      ],
    ];

    if (!empty($address_info['customer_address_id'])) {
      $pane_form['header']['title']['#markup'] = '<div class="title delivery-address-title">' . $this->t('deliver to') . '</div>';

      if ($entity = self::getAddressBookManager()->getUserAddressByCommerceId($address_info['customer_address_id'])) {
        $view_builder = \Drupal::entityTypeManager()->getViewBuilder('profile');
        $pane_form['address']['selected']['display'] = $view_builder->view($entity, 'teaser');

        GuestDeliveryHome::exposeSelectedDeliveryAddressToGtm($entity->get('field_address')->first()->getValue());

        $full_address = $this->getCheckoutHelper()->getFullAddressFromEntity($entity);
        $shipping_methods = GuestDeliveryHome::generateShippingEstimates($full_address);
        $default_shipping = $cart->getShippingMethodAsString();

        // Convert to code.
        /** @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_options_manager */
        $checkout_options_manager = \Drupal::service('alshaya_acm_checkout.options_manager');

        $default_shipping = $checkout_options_manager->getCleanShippingMethodCode($default_shipping);

        // Fetch translations for shipping methods.
        $shipping_method_translations = $checkout_options_manager->getShippingMethodTranslations();

        if (!empty($shipping_methods) && empty($default_shipping)) {
          $default_shipping = array_keys($shipping_methods)[0];
        }
      }
    }

    $shipping_methods_count_class = 'shipping-method-options-count-' . count($shipping_methods);

    $pane_form['address']['shipping_methods'] = [
      '#type' => 'radios',
      '#title' => count($shipping_methods) == 1 ? $this->t('delivery option') : $this->t('select delivery options'),
      '#default_value' => $default_shipping,
      '#validated' => TRUE,
      '#options' => $shipping_methods,
      '#prefix' => '<div id="shipping_methods_wrapper" class="' . $shipping_methods_count_class . '">',
      '#suffix' => '</div>',
      '#attributes' => ['class' => ['shipping-methods-container']],
    ];

    if (!empty($shipping_method_translations)) {
      $pane_form['address']['shipping_methods']['#attached']['drupalSettings']['alshaya_shipping_method_translations'] = $shipping_method_translations;
    }

    $complete_form['actions']['next']['#limit_validation_errors'] = [['address']];
    $complete_form['actions']['next']['#attributes']['class'][] = 'delivery-home-next';

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    if ($form_state->getValue('selected_tab') != 'checkout-home-delivery') {
      return;
    }

    // Validate only part of the form if we are saving address.
    if ($form_state->getTriggeringElement()['#name'] === 'save_address') {
      $this->saveAddressValidateCallback($complete_form, $form_state);

      // We will validate complete form later, not in this call.
      return;
    }

    $values = $form_state->getValue($pane_form['#parents']);

    $shipping_method = isset($values['address']['shipping_methods']) ? $values['address']['shipping_methods'] : NULL;

    if (empty($shipping_method)) {
      return;
    }

    try {
      $cart = $this->getCart();

      $address_info = $this->getAddressInfo('hd');
      $address = $address_info['address'];

      $entity = self::getAddressBookManager()->getUserAddressByCommerceId($address_info['customer_address_id']);
      if ($entity instanceof Profile) {
        $cart->setShipping($address);

        /** @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_options_manager */
        $checkout_options_manager = \Drupal::service('alshaya_acm_checkout.options_manager');
        $term = $checkout_options_manager->loadShippingMethod($shipping_method);
        $cart->setShippingMethod($term->get('field_shipping_carrier_code')->getString(), $term->get('field_shipping_method_code')->getString());

        // Clear the payment now.
        $this->getCheckoutHelper()->clearPayment();
      }
      else {
        \Drupal::logger('alshaya_acm_checkout')->error('Address in address book is not available for the user @user having address info @address_info for cart @cart', [
          '@user' => \Drupal::currentUser()->id(),
          '@address_info' => json_encode($address_info),
          '@cart' => json_encode((array) $cart->getShipping()),
        ]);
      }
    }
    catch (\Exception $e) {
      drupal_set_message($e->getMessage(), 'error');
      $form_state->setErrorByName('custom', $e->getMessage());
    }
  }

  /**
   * Validate callback to save new/updated address and use it for shipping.
   *
   * @param mixed|array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   */
  public function saveAddressValidateCallback($form, FormStateInterface $form_state) {
    // No further validations required if we already have some errors reported.
    if ($form_state->getErrors()) {
      return;
    }

    $values = $form_state->getValues();
    $address_values = $values['member_delivery_home']['address_form']['form'];
    $address_values['address_id'] = $values['member_delivery_home']['address_form']['address_id'];

    if (!empty($address_values['address_id'])) {
      /** @var \Drupal\profile\Entity\Profile $profile */
      $profile = self::getAddressBookManager()->getUserAddressByCommerceId($address_values['address_id']);
    }
    else {
      /** @var \Drupal\profile\Entity\Profile $profile */
      $profile = \Drupal::entityTypeManager()->getStorage('profile')->create([
        'type' => 'address_book',
      ]);
    }

    $profile->setOwnerId(\Drupal::currentUser()->id());
    $profile->get('field_address')->setValue($address_values);
    $profile->get('field_mobile_number')->setValue(_alshaya_acm_checkout_clean_address_phone($address_values['mobile_number']));

    /* @var \Drupal\Core\Entity\EntityConstraintViolationListInterface $violations */
    if ($violations = $profile->validate()) {
      foreach ($violations->getByFields(['field_address']) as $violation) {
        $error_field = explode('.', $violation->getPropertyPath());
        $form_state->setErrorByName('member_delivery_home][address_form][form][' . $error_field[2], $violation->getMessage());
      }
    }

    try {
      $cart = $this->getCart();

      if ($customer_address_id = self::getAddressBookManager()->pushUserAddressToApi($profile)) {
        $update = self::getCheckoutHelper()->getFullAddressFromEntity($profile);
        $this->getCheckoutHelper()->setCartShippingHistory(
          'hd',
          $update,
          ['customer_address_id' => $customer_address_id]
        );
        $cart->setShipping($update);
      }
    }
    catch (\Exception $e) {
      // Something might go wrong while updating cart.
      // We set temporary flag here which we use in ajax callback and
      // if there is an error we will just reload the page, code in page load
      // should handle the exception properly.
      // Error is already logged in API wrapper.
      $form_state->setTemporaryValue('cart_update_failed', 1);
    }
  }

  /**
   * Ajax callback to save address and use it for shipping.
   *
   * @param mixed|array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax response to reload page on successfully adding new address.
   */
  public function saveAddressAjaxCallback($form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    // If we face any API error while updating cart, we set temporary value
    // above, for such cases we just reload the page which is done below.
    if ($form_state->getErrors() && empty($form_state->getTemporaryValue('cart_update_failed'))) {
      $response->addCommand(new ReplaceCommand('#address-book-form-wrapper', $form['member_delivery_home']['address_form']));
      $response->addCommand(new InvokeCommand('#address-book-form-wrapper', 'show'));
      $response->addCommand(new InvokeCommand(NULL, 'firstErrorFocus', ['form.multistep-checkout .address-book-address', TRUE]));
      return $response;
    }

    \Drupal::moduleHandler()->alter(
      'home_delivery_save_address',
      $response,
      $this->getPluginId()
    );
    $response->addCommand(new InvokeCommand(NULL, 'showCheckoutLoader', []));
    $response->addCommand(new RedirectCommand(Url::fromRoute('acq_checkout.form', ['step' => 'delivery'], ['query' => ['method' => 'hd']])->toString()));

    return $response;
  }

}
