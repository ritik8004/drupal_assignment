<?php

namespace Drupal\alshaya_performance;

use Drupal\alshaya_performance\Logger\AlshayaPerformanceSysLog;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Class AlshayaPerformanceServiceProvider.
 */
class AlshayaPerformanceServiceProvider extends ServiceProviderBase implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    try {
      // Override the syslog logger class.
      $syslog = $container->getDefinition('logger.syslog');
      if ($syslog) {
        $syslog->setClass(AlshayaPerformanceSysLog::class);
      }

      // Enable dynamic page cache for all contexts.
      $renderer = $container->getParameter('renderer.config');
      $renderer['auto_placeholder_conditions']['contexts'][] = 'cookies:Drupal_visitor_acq_cart_id';
      $container->setParameter('renderer.config', $renderer);
    }
    catch (\Exception $e) {
      // Do nothing, system might still be installing or syslog module might
      // be disabled.
    }
  }

}
