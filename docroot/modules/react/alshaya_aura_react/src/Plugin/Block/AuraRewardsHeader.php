<?php

namespace Drupal\alshaya_aura_react\Plugin\Block;

use Drupal\alshaya_user\AlshayaUserInfo;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides 'AURA Rewards Header' block.
 *
 * @Block(
 *   id = "aura_rewards_header",
 *   admin_label = @Translation("AURA Rewards Header")
 * )
 */
class AuraRewardsHeader extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager service.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The entity type manager service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Alshaya User Info service object.
   *
   * @var \Drupal\alshaya_user\AlshayaUserInfo
   */
  protected $userInfo;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * AuraRewardsHeader constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The request object.
   * @param \Drupal\alshaya_user\AlshayaUserInfo $user_info
   *   The user info service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ConfigFactoryInterface $config_factory,
                              ClientInterface $http_client,
                              RequestStack $request,
                              AlshayaUserInfo $user_info,
                              LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->httpClient = $http_client;
    $this->request = $request;
    $this->userInfo = $user_info;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container,
                                array $configuration,
                                $plugin_id,
                                $plugin_definition) {
    return new static($configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('http_client'),
      $container->get('request_stack'),
      $container->get('alshaya_user.info'),
      $container->get('logger.channel.alshaya_aura_react')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'aura_rewards_header',
      '#learn_more_link' => $this->configFactory->get('alshaya_aura_react.settings')->get('aura_rewards_header_learn_more_link'),
      '#cache' => [
        'contexts' => [
          'user.roles:anonymous',
        ],
        'tags' => [
          'config:alshaya_aura_react.settings',
        ],
      ],
    ];
  }

}
