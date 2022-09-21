<?php

namespace Drupal\alshaya_ay_transac\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Url;

/**
 * Provides My Account block.
 *
 * @Block(
 *   id = "my_account_block",
 *   admin_label = @Translation("My Account block")
 * )
 */
class MyAccountBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current user service object.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  public $currentUser;

  /**
   * MyAccountBlock constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   The current account object.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              AccountProxy $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
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
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $user_id = $this->currentUser->id();
    if (empty($user_id) || $this->currentUser->isAnonymous()) {
      $url = Url::fromRoute('user.login')->toString();
      $data = "<a class='user-login' href=" . $url . "></a>";
    }
    else {
      $url = Url::fromRoute('entity.user.canonical', ['user' => $user_id])->toString();
      $data = "<a class='user-account' href=" . $url . "></a>";
    }

    return [
      '#markup' => '<div class="my-account-icon">' . $data . '</div>',
      '#cache' => ['contexts' => ['user']],
    ];
  }

}
