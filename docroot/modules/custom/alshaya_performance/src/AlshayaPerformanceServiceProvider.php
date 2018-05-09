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
    // Override the syslog logger class.
    $syslog = $container->getDefinition('logger.syslog');
    if ($syslog) {
      $syslog->setClass(AlshayaPerformanceSysLog::class);
    }
  }

}
