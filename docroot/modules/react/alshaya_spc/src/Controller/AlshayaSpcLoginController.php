<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\alshaya_social\AlshayaSocialHelper;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class Alshaya Spc Login Controller.
 *
 * @package Drupal\alshaya_spc\Controller
 */
class AlshayaSpcLoginController extends ControllerBase {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Social helper.
   *
   * @var \Drupal\alshaya_social\AlshayaSocialHelper
   */
  protected $socialHelper;

  /**
   * AlshayaSpcLoginController constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\alshaya_social\AlshayaSocialHelper $social_helper
   *   Social helper.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              AlshayaSocialHelper $social_helper) {
    $this->entityTypeManager = $entity_type_manager;
    $this->socialHelper = $social_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('alshaya_social.helper')
    );
  }

  /**
   * Cart login page.
   */
  public function checkoutLogin() {
    $build = [];
    if ($this->currentUser()->isAuthenticated()) {
      $response = new RedirectResponse(Url::fromRoute('alshaya_spc.checkout')->toString(), 302);
      $response->send();
      exit();
    }

    $build['login_form'] = [
      '#parents' => ['login_form'],
      '#type' => 'fieldset',
      '#title' => $this->t('sign in with email address'),
      '#prefix' => '<div class="checkout-login-wrapper"><div class="multistep-login"><div class="checkout-login-separator checkout-login-separator--email mobile-only-show"><span>' . $this->t('or') . '</span></div>',
      '#attributes' => [
        'class' => ['edit-checkout-login'],
      ],
    ];

    $build['login_form']['form'] = $this->formBuilder()->getForm('\Drupal\alshaya_spc\Form\AlshayaSpcLoginForm');

    $build['social_media'] = [
      '#parents' => ['social_media_auth_links'],
      '#type' => 'fieldset',
      '#title' => $this->t('sign in with social media'),
      '#attributes' => [
        'class' => ['social-signin-enabled', 'social-signup-form'],
      ],
      '#prefix' => '<div class="checkout-login-separator checkout-login-separator--social mobile-only-show"><span>' . $this->t('or') . '</span></div>',
      '#access' => $this->socialHelper->getStatus(),
    ];

    $build['social_media']['auth_links'] = [
      '#theme' => 'alshaya_social',
      '#social_networks' => $this->socialHelper->getSocialNetworks(),
      '#weight' => -1000,
    ];

    $config = $this->config('alshaya_acm_checkout.settings');

    $link = Link::createFromRoute(
      $this->t('checkout as guest'),
      'alshaya_spc.checkout',
      [],
      [
        'attributes' => [
          'gtm-type' => 'checkout-as-guest',
          'class' => 'edit-checkout-as-guest',
        ],
      ]
    );

    $build['checkout_as_guest'] = $link->toRenderable();
    $build['checkout_as_guest']['#prefix'] = '<div class="above-mobile-block">';
    $build['checkout_as_guest']['#suffix'] = '</div>';

    if (!empty($config->get('checkout_guest_email_usage.value'))) {
      $build['checkout_as_guest']['email_usage'] = [
        '#markup' => '<div class="checkout-guest-email-usage">' . $config->get('checkout_guest_email_usage.value') . '</div>',
      ];
    }

    if (!empty($config->get('checkout_guest_summary.value'))) {
      $build['checkout_as_guest']['summary'] = [
        '#markup' => '<div class="checkout-guest-summary">' . $config->get('checkout_guest_summary.value') . '</div>',
      ];
    }

    $build['actions'] = [
      '#type' => 'actions',
      '#weight' => 100,
      '#attributes' => [
        'class' => ['checkout-login-actions-wrapper'],
      ],
    ];

    $build['actions']['back_to_basket'] = [
      '#type' => 'link',
      '#title' => $this->t('Back to basket'),
      '#url' => Url::fromRoute('acq_cart.cart'),
      '#attributes' => [
        'class' => ['back-to-basket'],
      ],
      '#weight' => 99,
      '#suffix' => '</div></div>',
    ];

    $build['#cache']['tags'][] = 'config:alshaya_social.settings';
    $build['#cache']['tags'] = Cache::mergeTags($build['#cache']['tags'], $config->getCacheTags());
    $build['#attached'] = [
      'drupalSettings' => [
        'country_name' => function_exists('_alshaya_country_get_site_level_country_name')
        ? _alshaya_country_get_site_level_country_name()
        : '',
      ],
      'library' => [
        'alshaya_white_label/spc-login',
      ],
    ];

    $currency_config = $this->config('acq_commerce.currency');
    $build['#cache']['tags'] = Cache::mergeTags($build['#cache']['tags'], $currency_config->getCacheTags());

    $build['#attached']['drupalSettings']['alshaya_spc']['currency_config'] = [
      'currency_code' => $currency_config->get('currency_code'),
      'currency_code_position' => $currency_config->get('currency_code_position'),
      'decimal_points' => $currency_config->get('decimal_points'),
    ];

    $cart_config = $this->config('alshaya_acm.cart_config');
    $build['#cache']['tags'] = Cache::mergeTags($build['#cache']['tags'], $cart_config->getCacheTags());
    $build['#attached']['drupalSettings']['alshaya_spc']['cart_storage_expiration'] = $cart_config->get('cart_storage_expiration') ?? 15;

    $product_config = $this->config('alshaya_acm_product.settings');
    $build['#cache']['tags'] = Cache::mergeTags($build['#cache']['tags'], $product_config->getCacheTags());
    $build['#attached']['drupalSettings']['alshaya_spc']['productExpirationTime'] = $product_config->get('local_storage_cache_time') ?? 60;

    $this->moduleHandler()->alter('alshaya_spc_checkout_login_build', $build);

    return $build;
  }

}
