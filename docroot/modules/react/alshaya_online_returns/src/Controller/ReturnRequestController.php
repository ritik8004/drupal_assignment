<?php

namespace Drupal\alshaya_online_returns\Controller;

use Drupal\user\UserInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\alshaya_online_returns\Helper\OnlineReturnsHelper;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Return request controller to prepare data for return request page.
 */
class ReturnRequestController extends ControllerBase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Alshaya Online Returns Helper.
   *
   * @var \Drupal\alshaya_online_returns\Helper\OnlineReturnsHelper
   */
  protected $onlineReturnsHelper;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ReturnRequestController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\alshaya_online_returns\Helper\OnlineReturnsHelper $online_returns_helper
   *   Alshaya online returns helper.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              ModuleHandlerInterface $module_handler,
                              OnlineReturnsHelper $online_returns_helper,
                              EntityTypeManagerInterface $entity_type_manager) {
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->onlineReturnsHelper = $online_returns_helper;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('alshaya_online_returns.online_returns_helper'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Controller function for return request.
   *
   * @param \Drupal\user\UserInterface $user
   *   User object for which the orders detail page is being viewed.
   * @param string $order_id
   *   Order id to view the detail for.
   *
   * @return array|null
   *   Build array.
   */
  public function orderReturn(UserInterface $user, $order_id) {
    $config['enabled'] = $this->onlineReturnsHelper->isOnlineReturnsEnabled();
    $build['#cache']['tags'] = array_merge(
      $build['#cache']['tags'] ?? [],
      $this->configFactory->get('alshaya_online_returns.settings')->getCacheTags()
    );

    // Do not proceed if Online returns is not enabled.
    if ($config['enabled'] !== TRUE) {
      return;
    }

    $this->moduleHandler->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');

    // Get all the orders of logged in user.
    $customer_id = (int) $user->get('acq_customer_id')->getString();
    $orders = alshaya_acm_customer_get_user_orders($customer_id);

    if (empty($orders)) {
      return;
    }

    // Get the current order detail to build return page.
    $order_index = array_search($order_id, array_column($orders, 'increment_id'));

    if ($order_index === FALSE) {
      throw new NotFoundHttpException();
    }

    $order = $orders[$order_index];
    $orderDetails = alshaya_acm_customer_build_order_detail($order);

    // Get display image for each product.
    // Connverting image url to renderable drupal url.
    foreach ($orderDetails['#products'] as $key => $item) {
      if (!empty($item['image'])) {
        if ($item['image']['#theme'] == 'image_style') {
          $image_style = $this->entityTypeManager->getStorage('image_style');
          $data = [
            'url' => $image_style->load($item['image']['#style_name'])->buildUrl($item['image']['#uri']),
            'title' => $item['image']['#title'],
            'alt' => $item['image']['#alt'],
          ];
        }
        elseif ($item['image']['#theme'] == 'image') {
          $data = [
            'url' => $item['image']['#attributes']['src'],
            'title' => $item['image']['#attributes']['title'],
            'alt' => $item['image']['#attributes']['alt'],
          ];
        }
        $orderDetails['#products'][$key]['image_data'] = $data;
      }
    }

    // Attach library for return page react component.
    $build['#markup'] = '<div id="return-request"></div>';
    $build['#attached']['library'][] = 'alshaya_online_returns/alshaya_return_requests';
    $build['#attached']['drupalSettings'] = [
      'onlineReturns' => $config,
      'orderDetails' => $orderDetails,
    ];
    return $build;
  }

}
