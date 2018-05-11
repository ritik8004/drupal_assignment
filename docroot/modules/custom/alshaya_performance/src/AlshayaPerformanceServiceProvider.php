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
    }
    catch (\Exception $e) {
      // Do nothing, system might still be installing or syslog module might
      // be disabled.
    }
  }

}
