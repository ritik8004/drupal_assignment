<?php

namespace Drupal\alshaya_acm_customer\Plugin\Block;

use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Url;
use Drupal\acq_commerce\Conductor\APIWrapper;
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
   * Commerce conductor service.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  protected $commerceConductor;

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
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $commerce_conductor
   *   Commerce conductor service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountProxyInterface $current_account, RouteMatchInterface $route_match, APIWrapper $commerce_conductor) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_account;
    $this->routeMatch = $route_match;
    $this->commerceConductor = $commerce_conductor;
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
      $container->get('acq_commerce.api')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $email = $this->currentUser->getEmail();
    $uid = $this->currentUser->id();

    try {
      // Get the orders of the user.
      $orders = $this->commerceConductor->getCustomerOrders($email);
      $orders = array_slice($orders, 0, 3);

      // Recent order text.
      $build['recent_order_title'] = [
        '#markup' => '<h2 class="subtitle">' . $this->t("Recent Orders") . '</h2>',
      ];

      // If no order available for the user.
      if (empty($orders)) {
        $build['no_order_description'] = [
          '#markup' => '<div>' . $this->t("You don't have any orders to display.") . '</div>',
        ];
        $build['go_shopping'] = [
          '#type' => 'link',
          '#title' => $this->t('GO SHOPPING'),
          '#url' => Url::fromRoute('entity.user.canonical', ['user' => $uid]),
        ];
      }
      else {
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

            // Todo: Update order id and date once available from the conductor.
            $order['id'] = 1;
            $order['date'] = \Drupal::service('date.formatter')->format(time(), 'Y-m-d');
            $order['user_id'] = $uid;

            // Theme the order total grands with currency.
            $order['total']['grands'] = [
              '#theme' => 'alshaya_acm_price',
              '#price' => isset($order['total']) ? $order['total']['grands'] : 0,
            ];

            // Iterate over each order item.
            foreach ($order['items'] as $key => $item) {
              // Load SKU to get the image.
              $sku = SKU::loadFromSku($item['sku']);
              if ($sku) {
                $sku_image = $sku->attr_image->view('product_list');
                $order['items'][$key]['sku_attr_image'] = $sku_image;
              }

              // Total price.
              $order['items'][$key]['total_price'] = [
                '#theme' => 'alshaya_acm_price',
                '#price' => $order['items'][$key]['price'] * $order['items'][$key]['ordered'],
              ];

              // Unit price.
              $order['items'][$key]['price'] = [
                '#theme' => 'alshaya_acm_price',
                '#price' => $order['items'][$key]['price'],
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
    return Cache::mergeContexts(parent::getCacheContexts(), ['user']);
  }

}
