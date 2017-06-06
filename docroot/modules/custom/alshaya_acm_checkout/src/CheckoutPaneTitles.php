<?php

namespace Drupal\alshaya_acm_checkout;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Multi step checkout panes custom titles.
 */
class CheckoutPaneTitles extends ControllerBase {

  /**
   * The Plugin instance of CheckoutFlowManager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   *
   * @see \Drupal\acq_checkout\CheckoutFlowManager
   */
  protected $plugin;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * CheckoutPaneTitles constructor.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $checkout_flow
   *   The config factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(PluginManagerInterface $checkout_flow, ConfigFactoryInterface $config_factory) {
    $this->plugin = $checkout_flow;
    $this->config = $config_factory->get('acq_checkout.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.acq_checkout_flow'),
      $container->get('config.factory')
    );
  }

  /**
   * Page title for checkout steps page.
   */
  public function checkoutPageTitle(RouteMatchInterface $route_match) {
    // Current checkout step.
    $current_step = $route_match->getParameter('step');
    // Get the list of all available checkout steps.
    $checkoutFlowPlugin = $this->config->get('checkout_flow_plugin') ?: 'multistep_default';
    $pluginObj = $this->plugin->createInstance($checkoutFlowPlugin, ['validate_current_step' => TRUE]);
    $steps = $pluginObj->getVisibleSteps();
    // Get the title of the current checkout step.
    if (!empty($steps[$current_step]) && !empty($steps[$current_step]['title'])) {
      return $steps[$current_step]['title'];
    }
    return t('Checkout');
  }

}
