<?php

namespace Drupal\rcs_placeholders\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\rcs_placeholders\Event\RcsPhPathProcessorEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a path processor to detect the commerce entities page types.
 *
 * @property \Drupal\Core\Entity\EntityStorageInterface nodeStorage
 */
class RcsPhPathProcessor implements InboundPathProcessorInterface {

  /**
   * Mapping of entity path and url alias.
   *
   * @var array
   */
  protected static $processedPaths = [];

  /**
   * RCS Entity Type.
   *
   * @var string
   */
  public static $entityType = NULL;

  /**
   * RCS Entity Path.
   *
   * @var string
   */
  public static $entityPath;

  /**
   * RCS Entity Path Prefix.
   *
   * It is stored from config here.
   * Allow using this directly from the variable in other places.
   *
   * @var string
   */
  public static $entityPathPrefix;

  /**
   * RCS Entity data.
   *
   * @var array|null
   */
  public static $entityData;

  /**
   * RCS path to check.
   *
   * @var string
   */
  public static $rcsPathToCheck;

  /**
   * RCS Full Path.
   *
   * @var string
   */
  public static $entityFullPath;

  /**
   * RCS Full Path.
   *
   * @var string
   */
  public static $pageFullPath;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new RcsPhPathProcessor instance.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    LanguageManagerInterface $language_manager,
    ModuleHandlerInterface $module_handler,
    ConfigFactoryInterface $config_factory,
    EventDispatcherInterface $event_dispatcher,
    RequestStack $request_stack
  ) {
    $this->languageManager = $language_manager;
    $this->moduleHandler = $module_handler;
    $this->configFactory = $config_factory;
    $this->eventDispatcher = $event_dispatcher;
    $this->requestStack = $request_stack;
  }

  /**
   * Alters the path for commerce entities.
   *
   * Look for commerce entities prefix in URL and render the associated
   * placeholder entity.
   *
   * @param string $path
   *   The path to process, with a leading slash.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HttpRequest object representing the request to process. Note, if this
   *   method is being called via the path_processor_manager service and is not
   *   part of routing, the current request object must be cloned before being
   *   passed in.
   *
   * @return string
   *   The processed path.
   */
  public function processInbound($path, Request $request) {
    // @todo Move the whole logic to use Custom Event and Event Subscribers.
    // Use static cache to improve performance.
    if (isset(self::$processedPaths[$path])) {
      return self::$processedPaths[$path];
    }

    // Remove language code from URL.
    $full_path = $rcs_path_to_check = self::getFullPagePath($request);

    self::$rcsPathToCheck = $rcs_path_to_check;

    $event = new RcsPhPathProcessorEvent($rcs_path_to_check);
    $event->setData([
      'path' => $path,
      'fullPath' => $full_path,
      'pathToCheck' => $rcs_path_to_check,
      'langcode' => $this->languageManager->getCurrentLanguage()->getId(),
    ]);
    $this->eventDispatcher->dispatch($event, RcsPhPathProcessorEvent::ALTER);

    $event_data = $event->getData();

    if (!empty($event_data['entityType'])
      && $this->requestStack->getMainRequest()->getUri() === $request->getUri()
    ) {
      self::$entityType = $event_data['entityType'];
      self::$entityPath = $event_data['entityPath'];
      self::$entityPathPrefix = $event_data['entityPathPrefix'];
      self::$entityFullPath = $event_data['entityFullPath'];
      self::$processedPaths[$rcs_path_to_check] = $event_data['processedPaths'];
      if (!empty($event_data['entityData'])) {
        self::$entityData = $event_data['entityData'];
      }

      return self::$processedPaths[$full_path];
    }

    // Set current path as default so we do not process twice for same path.
    if (empty(self::$processedPaths[$path])) {
      self::$processedPaths[$path] = $event_data['path'] ?? $path;
    }

    return self::$processedPaths[$path];
  }

  /**
   * Process the full page path from request and return it without the langcode.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Optional request object of the page.
   *
   * @return string
   *   Page Full Path.
   */
  public static function getFullPagePath(Request $request = NULL): string {
    if (empty($request)) {
      $request = \Drupal::request();
    }
    // The $path value has been processed in case the requested url is the
    // alias of an existing technical path. For example, $path may be /node/12
    // if the requested url /buy-my-product is an alias for node 12.
    // For this reason, we use $request->getPathInfo() to get the real
    // requested url instead of $path.
    $page_full_path = str_replace(
      '/' . \Drupal::languageManager()->getCurrentLanguage()->getId() . '/',
      '/',
      $request->getPathInfo()
    );

    return $page_full_path;
  }

  /**
   * Returns TRUE if we are on RCS page.
   *
   * @return bool
   *   Returns TRUE if its Rcs page.
   */
  public static function isRcsPage() {
    return self::$entityType != NULL;
  }

  /**
   * Returns RCS page type.
   *
   * @return string
   *   Returns Rcs page type.
   */
  public static function getRcsPageType() {
    return self::$entityType;
  }

  /**
   * Returns the flipped mapping of entity path and path alias.
   *
   * @param string $path
   *   The entity path.
   *
   * @return string
   *   Returns the path alias of the entity path.
   */
  public static function getOrignalPathFromProcessed(string $path): string {
    $processed_paths = array_flip(self::$processedPaths);
    return $processed_paths[$path] ?? $path;
  }

  /**
   * Returns full path with prefix.
   *
   * @param bool $trim
   *   Trim the front slash from start and end.
   *
   * @return string
   *   Full path with prefix if available.
   */
  public static function getFullPath(bool $trim = TRUE) {
    if (empty(self::$entityType)) {
      return '';
    }
    $url = self::$entityFullPath;
    // Trim the front slash.
    if ($trim) {
      $url = trim($url, '/');
    }

    return $url;
  }

  /**
   * Returns url key.
   *
   * @param bool $trim
   *   Trim the front slash from start and end.
   *
   * @return string
   *   The URL key of the current entity path.
   */
  public static function getUrlKey(bool $trim = TRUE) {
    $url = self::$entityPath;
    // Trim the front slash.
    if ($trim) {
      $url = trim($url, '/');
    }

    return $url;
  }

}
