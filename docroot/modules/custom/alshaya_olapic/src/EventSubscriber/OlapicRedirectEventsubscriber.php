<?php

namespace Drupal\alshaya_olapic\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\CurrentPathStack;

/**
 * Class OlapicRedirect.
 */
class OlapicRedirectEventsubscriber implements EventSubscriberInterface {

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * Creates a CustomLogoBlock instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path stack.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
  CurrentPathStack $current_path) {
    $this->configFactory = $config_factory;
    $this->currentPath = $current_path;

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['redirectPage', 100];
    return $events;
  }

  /**
   * Code that should be triggered on event specified.
   */
  public function redirectPage(GetResponseEvent $event) {
    // Check current path.
    // $current_path = $this->currentPath->getPath();.
    // $development_mode = $this->configFactory->get('alshaya_olapic.settings')->get('development_mode');.
  }

}
