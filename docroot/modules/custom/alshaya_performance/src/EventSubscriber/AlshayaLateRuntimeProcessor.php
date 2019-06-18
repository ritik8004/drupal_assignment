<?php

namespace Drupal\alshaya_performance\EventSubscriber;

use Drupal\purge_processor_lateruntime\EventSubscriber\LateRuntimeProcessor;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;

/**
 * Class AlshayaLateRuntimeProcessor.
 *
 * @package Drupal\alshaya_performance\EventSubscriber
 */
class AlshayaLateRuntimeProcessor extends LateRuntimeProcessor {

  /**
   * {@inheritdoc}
   */
  public function onKernelFinishRequest(FinishRequestEvent $event) {
    // Do nothing, we want to disable late runtime processor.
  }

}
