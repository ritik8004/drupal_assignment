<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
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
    if (\Drupal::currentUser()->isAnonymous()) {
      return $pane_form;
    }

    $pane_form['#suffix'] = '<div class="fieldsets-separator">' . $this->t('OR') . '</div>';

    $cart = $this->getCart();
    $address = (array) $cart->getShipping();

    // This class is required to make theme work properly.
    $pane_form['#attributes']['class'] = 'c-address-book';

    $pane_form['address_form'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'address-book-form-wrapper',
      ],
    ];

    $pane_form['address_form']['title'] = [
      '#markup' => '<div class="title delivery-address-form-title">' . $this->t('add new address') . '</div>',
    ];

    $pane_form['address_form']['address_id'] = [
      '#type' => 'hidden',
      '#value' => '',
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

    if ($address['customer_address_id']) {
      $pane_form['header']['title']['#markup'] = '<div class="title delivery-address-title">' . $this->t('delivery address') . '</div>';

      /** @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager */
      $address_book_manager = \Drupal::service('alshaya_addressbook.manager');

      if ($entity = $address_book_manager->getUserAddressByCommerceId($address['customer_address_id'])) {
        $view_builder = \Drupal::entityTypeManager()->getViewBuilder('profile');
        $pane_form['address']['selected']['display'] = $view_builder->view($entity, 'teaser');

        $shipping_methods = self::generateShippingEstimates($entity);
        $default_shipping = $cart->getShippingMethodAsString();

        // Convert to code.
        $default_shipping = str_replace(',', '_', substr($default_shipping, 0, 32));

        if (!empty($shipping_methods) && empty($default_shipping)) {
          $default_shipping = array_keys($shipping_methods)[0];
        }
      }
    }

    $pane_form['address']['shipping_methods'] = [
      '#type' => 'radios',
      '#title' => $this->t('select delivery options'),
      '#default_value' => $default_shipping,
      '#validated' => TRUE,
      '#options' => $shipping_methods,
      '#prefix' => '<div id="shipping_methods_wrapper">',
      '#suffix' => '</div>',
    ];

    $complete_form['actions']['next']['#limit_validation_errors'] = [['address']];

    $complete_form['actions']['back_to_basket'] = [
      '#type' => 'link',
      '#title' => $this->t('Back to basket'),
      '#url' => Url::fromRoute('acq_cart.cart'),
      '#attributes' => [
        'class' => ['back-to-basket'],
      ],
    ];

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValue($pane_form['#parents']);

    $shipping_method = isset($values['address']['shipping_methods']) ? $values['address']['shipping_methods'] : NULL;

    if (empty($shipping_method)) {
      return;
    }

    $cart = $this->getCart();

    $address = (array) $cart->getShipping();

    /** @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager */
    $address_book_manager = \Drupal::service('alshaya_addressbook.manager');
    $entity = $address_book_manager->getUserAddressByCommerceId($address['customer_address_id']);
    $address = $address_book_manager->getAddressFromEntity($entity, FALSE);

    $update = [];
    $update['customer_address_id'] = $address['customer_address_id'];
    $update['country'] = $address['country_id'];
    $update['country_id'] = $address['country_id'];
    $update['customer_id'] = $cart->customerId();

    $cart->setShipping($update);

    /** @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_options_manager */
    $checkout_options_manager = \Drupal::service('alshaya_acm_checkout.options_manager');
    $term = $checkout_options_manager->loadShippingMethod($shipping_method);
    $cart->setShippingMethod($term->get('field_shipping_carrier_code')->getString(), $term->get('field_shipping_method_code')->getString());
  }

  /**
   * Helper function to get shipping estimates.
   *
   * @param \Drupal\profile\Entity\Profile $entity
   *   Address entity.
   *
   * @return array
   *   Available shipping methods.
   */
  public static function generateShippingEstimates(Profile $entity) {
    /** @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager */
    $address_book_manager = \Drupal::service('alshaya_addressbook.manager');
    $full_address = $address_book_manager->getAddressFromEntity($entity, FALSE);
    return GuestDeliveryHome::generateShippingEstimates($full_address);
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
    if ($form_state->getErrors()) {
      return $form;
    }

    /** @var \Drupal\profile\ProfileStorage $profile_storage */
    $profile_storage = \Drupal::entityTypeManager()->getStorage('profile');

    /** @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager */
    $address_book_manager = \Drupal::service('alshaya_addressbook.manager');

    $values = $form_state->getValues();
    $address_values = $values['member_delivery_home']['address_form']['form'];

    if (!empty($address_values['address_id'])) {
      /** @var \Drupal\profile\Entity\Profile $profile */
      $profile = $address_book_manager->getUserAddressByCommerceId($address_values['address_id']);
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

    if ($form_state->getErrors()) {
      return $form;
    }

    $cart = $this->getCart();

    if ($customer_address_id = $address_book_manager->pushUserAddressToApi($profile)) {
      $update = [];
      $update['customer_address_id'] = $customer_address_id;
      $update['country_id'] = $address_values['country_code'];
      $update['country'] = $address_values['country_code'];
      $update['customer_id'] = $cart->customerId();

      $cart->setShipping($update);
    }

    $response = new AjaxResponse();
    $response->addCommand(new RedirectCommand(Url::fromRoute('acq_checkout.form', ['step' => 'delivery'])->toString()));
    return $response;
  }

}
