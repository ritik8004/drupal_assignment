<?php

namespace Drupal\alshaya_acm_customer\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Url;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides recent order block for the user.
 *
 * @Block(
 *   id = "alshaya_user_recent_orders",
 *   admin_label = @Translation("User recent orders")
 * )
 */
class UserRecentOrders extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current account object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Date time formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * The logger instance.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * UserRecentOrders constructor.
   *
   * @param array $configuration
   *   Configuration data.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin defination.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_account
   *   The current account object.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match object.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   Date time formatter service.
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   The current request.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The logger instance.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              AccountProxyInterface $current_account,
                              RouteMatchInterface $route_match,
                              ModuleHandlerInterface $module_handler,
                              DateFormatterInterface $date_formatter,
                              Request $current_request,
                              LoggerChannelInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_account;
    $this->routeMatch = $route_match;
    $this->moduleHandler = $module_handler;
    $this->dateFormatter = $date_formatter;
    $this->currentRequest = $current_request;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('current_route_match'),
      $container->get('module_handler'),
      $container->get('date.formatter'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('logger.factory')->get('alshaya_acm_customer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $this->moduleHandler->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');
    $this->moduleHandler->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');

    $build = [];

    // Get user id of user who's profile is currently visit.
    $account = $this->currentRequest->attributes->get('user');
    if (empty($account)) {
      $account = $this->currentUser;
    }

    if (!alshaya_acm_customer_is_customer($account)) {
      return [];
    }

    $email = $account->getEmail();
    $uid = $account->id();

    try {

      $build['edit_account'] = [
        '#type' => 'link',
        '#title' => $this->t('edit account details'),
        '#url' => Url::fromRoute('entity.user.edit_form', ['user' => $uid]),
        '#attributes' => [
          'class' => ['button', 'button-wide', 'edit-account'],
        ],
      ];

      // Get the orders of the user.
      $orders = alshaya_acm_customer_get_user_orders($email);

      // Recent order text.
      $build['recent_order_title'] = [
        '#markup' => '<h2 class="subtitle">' . $this->t("recent orders") . '</h2>',
      ];

      // If no order available for the user.
      if (empty($orders)) {
        $build['no_order_description'] = [
          '#markup' => '<div class="no--orders">' . $this->t("You have no recent orders to display.") . '</div>',
        ];
        $build['go_shopping'] = [
          '#type' => 'link',
          '#title' => $this->t('go shopping'),
          '#url' => Url::fromRoute('<front>'),
        ];
      }
      else {
        // Sort the order in the basis of order date and show recent 3.
        $orders = array_slice($orders, 0, 3);

        // All order link.
        $build['view_all_orders'] = [
          '#type' => 'link',
          '#attributes' => ['class' => ['show-all']],
          '#title' => $this->t('View all orders'),
          '#url' => Url::fromRoute('acq_customer.orders', ['user' => $uid]),
        ];

        foreach ($orders as $order) {
          // If order has some items.
          if (!empty($order['items'])) {
            $order['item_names'] = [];
            $order['user_id'] = $uid;
            $order['date'] = alshaya_master_get_site_date_from_api_date($order['created_at'], 'order_date');
            $order['id'] = $order['increment_id'];

            // Theme the order total grands with currency.
            $order['totals']['grand'] = [
              '#theme' => 'acq_commerce_price',
              '#price' => isset($order['totals']) ? $order['totals']['grand'] : 0,
            ];

            // Iterate over each order item.
            foreach ($order['items'] as $key => $item) {
              $order['item_names'][] = $item['name'];

              // Load the first image.
              $order['items'][$key]['image'] = alshaya_acm_get_product_display_image($item['sku'], '291x288', 'order_detail');

              // Total price.
              $order['items'][$key]['total_price'] = [
                '#theme' => 'acq_commerce_price',
                '#price' => ($order['items'][$key]['price'] * $order['items'][$key]['ordered']),
              ];

              // Unit price.
              $order['items'][$key]['price'] = [
                '#theme' => 'acq_commerce_price',
                '#price' => $order['items'][$key]['price'],
              ];
            }

            $order['status'] = alshaya_acm_customer_get_order_status($order);
          }

          $build['recent_order'][] = [
            '#theme' => 'user_recent_order',
            '#order' => $order,
          ];
        }
      }
    }
    catch (\Exception $e) {
      // If any error during api/service calling.
      $this->logger->error($e->getMessage());

      if (acq_commerce_is_exception_api_down_exception($e)) {
        $build['message'] = [
          '#theme' => 'global_error',
          '#message' => $e->getMessage(),
        ];
      }
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Get uid of current user.
    $uid = $this->currentUser->id();
    return Cache::mergeTags(parent::getCacheTags(), ['user:' . $uid . ':orders']);
  }

}
