<?php

namespace Drupal\alshaya_acm_customer\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Url;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountProxyInterface $current_account, RouteMatchInterface $route_match, ModuleHandlerInterface $module_handler, DateFormatterInterface $date_formatter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_account;
    $this->routeMatch = $route_match;
    $this->moduleHandler = $module_handler;
    $this->dateFormatter = $date_formatter;
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
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    \Drupal::moduleHandler()->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');

    $build = [];

    // Get user id of user who's profile is currently visit.
    $account = \Drupal::request()->attributes->get('user');
    if (empty($account)) {
      $account = $this->currentUser;
    }

    $email = $account->getEmail();
    $uid = $account->id();

    try {

      $build['edit_account'] = [
        '#type' => 'link',
        '#title' => $this->t('Edit account details'),
        '#url' => Url::fromRoute('entity.user.edit_form', ['user' => $uid]),
        '#attributes' => [
          'class' => ['button'],
        ],
      ];

      // Get the orders of the user.
      $orders = alshaya_acm_customer_get_user_orders($email);

      // Recent order text.
      $build['recent_order_title'] = [
        '#markup' => '<h2 class="subtitle">' . $this->t("Recent Orders") . '</h2>',
      ];

      // If no order available for the user.
      if (empty($orders)) {
        $build['no_order_description'] = [
          '#markup' => '<div class="no--orders">' . $this->t("You don't have any orders to display.") . '</div>',
        ];
        $build['go_shopping'] = [
          '#type' => 'link',
          '#title' => $this->t('GO SHOPPING'),
          '#url' => Url::fromRoute('<front>'),
        ];
      }
      else {
        // Sort the order in the basis of order date and show recent 3.
        $orders = array_slice($orders, 0, 3);

        // All order link.
        $build['view_all_orders'] = [
          '#type' => 'link',
          'class' => ['show-all'],
          '#title' => $this->t('View all orders'),
          '#url' => Url::fromRoute('acq_customer.orders', ['user' => $uid]),
        ];

        foreach ($orders as $order) {
          // If order has some items.
          if (!empty($order['items'])) {
            $order['item_names'] = [];
            $order['user_id'] = $uid;
            $order['date'] = $this->dateFormatter->format(strtotime($order['created_at']), 'order_date');
            $order['id'] = $order['increment_id'];

            // Theme the order total grands with currency.
            $order['totals']['grand'] = [
              '#theme' => 'alshaya_acm_price',
              '#price' => isset($order['totals']) ? number_format($order['totals']['grand'], 3) : 0,
            ];

            // Iterate over each order item.
            foreach ($order['items'] as $key => $item) {
              $this->moduleHandler->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');
              // Load sku from item_id that we have in $item.
              $media = alshaya_acm_product_get_sku_media($item['sku']);
              if (!empty($media)) {
                $image = array_shift($media);
                $file_uri = $image->getFileUri();
                $order['items'][$key]['sku_attr_image'] = [
                  '#theme' => 'image_style',
                  '#style_name' => 'checkout_summary_block_thumbnail',
                  '#uri' => $file_uri,
                ];
              }

              // Total price.
              $order['items'][$key]['total_price'] = [
                '#theme' => 'alshaya_acm_price',
                '#price' => number_format($order['items'][$key]['price'] * $order['items'][$key]['ordered'], 3),
              ];

              // Unit price.
              $order['items'][$key]['price'] = [
                '#theme' => 'alshaya_acm_price',
                '#price' => number_format($order['items'][$key]['price'], 3),
              ];

              // Item name for order.
              $order['item_names'][] = $order['items'][$key]['name'];
            }
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
      \Drupal::logger('alshaya_acm_customer')->error($e->getMessage());
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

}
