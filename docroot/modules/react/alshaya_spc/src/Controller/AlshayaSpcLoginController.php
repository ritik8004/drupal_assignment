<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\alshaya_social\AlshayaSocialHelper;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AlshayaSpcLoginController.
 *
 * @package Drupal\alshaya_spc\Controller
 */
class AlshayaSpcLoginController extends ControllerBase {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\alshaya_social\AlshayaSocialHelper $social_helper
   *   Social helper.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager,
    AlshayaSocialHelper $social_helper
  ) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->socialHelper = $social_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('alshaya_social.helper')
    );
  }

  /**
   * Cart login page.
   */
  public function login() {
    $build['login_form'] = [
      '#parents' => ['login_form'],
      '#type' => 'fieldset',
      '#title' => $this->t('sign in with email address'),
    ];

    $build['login_form']['form'] = $this->formBuilder()->getForm('\Drupal\alshaya_spc\Form\AlshayaSpcLoginForm');

    $build['social_media'] = [
      '#parents' => ['social_media_auth_links'],
      '#type' => 'fieldset',
      '#title' => $this->t('sign in with email address'),
      '#attributes' => [
        'class' => ['social-signin-enabled', 'social-signup-form'],
      ],
      '#prefix' => '<div class="checkout-login-separator order-1"><span>' . $this->t('or') . '</span></div>',
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
    ];

    $build['#cache']['tags'][] = 'config:alshaya_social.settings';
    $build['#cache']['tags'][] = 'config:alshaya_acm_checkout.settings';
    $build['#attached'] = [
      'library' => [
        'alshaya_spc/cart_validate',
      ],
    ];
    return $build;
  }

}
