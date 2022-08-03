<?php

namespace Drupal\alshaya_performance;

use Drupal\alshaya_performance\EventSubscriber\AlshayaLateRuntimeProcessor;
use Drupal\alshaya_performance\Logger\AlshayaPerformanceSysLog;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Class Alshaya Performance Service Provider.
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

      // Override purge late runtime processor.
      $purge_late_runtime = $container->getDefinition('purge_processor_lateruntime.processor');
      if ($purge_late_runtime) {
        $purge_late_runtime->setClass(AlshayaLateRuntimeProcessor::class);
      }
    }
    catch (\Exception) {
      // Do nothing, system might still be installing or module might
      // be disabled.
    }
  }

}
