<?php

namespace Drupal\alshaya_bazaar_voice\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice;
use Drupal\node\NodeInterface;

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
   * Array of cache tags.
   *
   * @var array
   */
  protected $cacheTags = [];

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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountProxyInterface $current_user, RouteMatchInterface $route_match, AlshayaBazaarVoice $alshayaBazaarVoice) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
    $this->routeMatch = $route_match;
    $this->alshayaBazaarVoice = $alshayaBazaarVoice;
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
      $container->get('alshaya_bazaar_voice.service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $user_details = [
      'userId' => $this->currentUser->id(),
      'emailId' => $this->currentUser->getEmail(),
    ];
    if ($this->routeMatch->getRouteName() === 'entity.node.canonical') {
      $node = $this->routeMatch->getParameter('node');
      if ($node instanceof NodeInterface && $node->bundle() === 'acq_product') {
        // Add user review of current product in user settings.
        $user_details['productReview'] = $this->alshayaBazaarVoice->getProductReviewForCurrentUser($node);
      }
    }
    $build['#attached']['drupalSettings']['bazaarvoiceUserDetails'] = $user_details;
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    if ($this->routeMatch->getRouteName() === 'entity.node.canonical') {
      $node = $this->routeMatch->getParameter('node');
      if ($node->bundle() === 'acq_product') {
        $this->cacheTags[] = 'node:' . $node->id();
      }
    }
    if ($this->currentUser->isAuthenticated()) {
      $this->cacheTags[] = 'user:' . $this->currentUser->id();
    }
    return Cache::mergeTags(
      parent::getCacheTags(),
      $this->cacheTags
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url.path']);
  }

}
