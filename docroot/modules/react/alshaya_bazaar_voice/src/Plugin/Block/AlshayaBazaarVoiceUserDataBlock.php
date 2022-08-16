<?php

namespace Drupal\alshaya_bazaar_voice\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice;
use Drupal\node\NodeInterface;
use Drupal\Core\Cache\Cache;
use Drupal\alshaya_acm_product\SkuManager;

/**
 * Provides a block for bazaarvoice user data.
 *
 * @Block(
 *   id = "alshaya_bazaarvoice_user_data_block",
 *   admin_label = @Translation("Bazaarvoice User data block"),
 * )
 */
class AlshayaBazaarVoiceUserDataBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Alshaya bazaar voice service object.
   *
   * @var \Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice
   */
  protected $alshayaBazaarVoice;

  /**
   * SKU Manager service object.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * AlshayaBazaarVoiceUserDataBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Current user.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match service.
   * @param \Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice $alshayaBazaarVoice
   *   Alshaya bazaar voice service.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager service object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountProxyInterface $current_user, RouteMatchInterface $route_match, AlshayaBazaarVoice $alshayaBazaarVoice, SkuManager $sku_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
    $this->routeMatch = $route_match;
    $this->alshayaBazaarVoice = $alshayaBazaarVoice;
    $this->skuManager = $sku_manager;
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
      $container->get('alshaya_bazaar_voice.service'),
      $container->get('alshaya_acm_product.skumanager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $user_details = [
      'userId' => alshaya_acm_customer_is_customer($this->currentUser, TRUE),
      'emailId' => $this->currentUser->getEmail(),
    ];
    $product_review_data = NULL;
    if ($this->currentUser->isAuthenticated() && $this->routeMatch->getRouteName() === 'entity.node.canonical') {
      $node = $this->routeMatch->getParameter('node');
      if ($node instanceof NodeInterface && $node->bundle() === 'acq_product') {
        // Add user review of current product in user settings.
        $sku_id = $this->skuManager->getSkuForNode($node);
        $product_review_data = $this->alshayaBazaarVoice->getProductReviewForCurrentUser($sku_id);
      }
    }
    $user_details['productReview'] = $product_review_data;
    $build['#attached']['drupalSettings']['bazaarvoiceUserDetails'] = $user_details;
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['user.roles:authenticated']);
  }

}
