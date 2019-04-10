<?php

namespace Drupal\alshaya_seo_transac\Controller;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class AlshayaSeoController.
 */
class AlshayaSeoController extends ControllerBase {

  /**
   * Product Category Tree service object.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  protected $productCategoryTree;

  /**
   * Module Handler service object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The Entity Type Manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * AlshayaSeoController constructor.
   *
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $product_category_tree
   *   Product Category Tree service object.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Manager service.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   Current User service.
   */
  public function __construct(ProductCategoryTree $product_category_tree,
                              ModuleHandlerInterface $module_handler,
                              EntityTypeManagerInterface $entityTypeManager,
                              AccountProxyInterface $currentUser) {
    $this->productCategoryTree = $product_category_tree;
    $this->moduleHandler = $module_handler;
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_acm_product_category.product_category_tree'),
      $container->get('module_handler'),
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * Controller for the site map.
   */
  public function siteMap() {
    $data = $this->productCategoryTree->getCategoryTreeCached();

    $build = [
      '#theme' => 'alshaya_sitemap',
      '#term_tree' => $data,
    ];

    // Discard cache for the page once a term gets updated.
    $build['#cache']['tags'][] = ProductCategoryTree::CACHE_TAG;

    return $build;
  }

  /**
   * Get current user details for dataLayer.
   */
  public function getCurrentUserDetails() {
    $this->moduleHandler->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');
    $current_user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    $email = $current_user->get('mail')->getString();
    $customer_type = count(alshaya_acm_customer_get_user_orders($email)) > 1 ? 'Repeat Customer' : 'New Customer';
    $privilege_customer = 'Regular Customer';
    if (!empty($current_user->get('field_privilege_card_number')->getString())) {
      $privilege_customer = 'Privilege Customer';
    }

    $user_details = [
      'userID' => $current_user->get('uid')->getString(),
      'userEmailID' => $email,
      'userName' => $current_user->get('field_first_name')->getString() . ' ' . $current_user->get('field_last_name')->getString(),
      'userType' => 'Logged in User',
      'customerType' => $customer_type,
      'privilegeCustomer' => $privilege_customer,
    ];

    return new JsonResponse(json_encode($user_details));
  }

}
