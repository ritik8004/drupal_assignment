<?php

namespace Drupal\alshaya_feed\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AlshayaFeedController.
 *
 * @package Drupal\alshaya_feed\Controller
 */
class AlshayaFeedController extends ControllerBase {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * AlshayaFeedController constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   ConfigFactoryInterface object.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct(
    LanguageManagerInterface $language_manager,
    ConfigFactoryInterface $config_factory,
    LoggerInterface $logger
  ) {
    $this->languageManager = $language_manager;
    $this->configFactory = $config_factory;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('language_manager'),
      $container->get('config.factory'),
      $container->get('logger.channel.alshaya_feed')
    );
  }

  /**
   * Returns the whole xml feed.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\Response|false
   *   Returns an XML response.
   */
  public function getFeed(Request $request) {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $file = file_url_transform_relative(file_create_url(file_default_scheme() . '://feed_' . $langcode . '.xml'));
    $output = FALSE;
    if (file_exists(ltrim($file, '/'))) {
      $output = file_get_contents(ltrim($file, '/'));
    }

    if (!$output) {
      $this->logger->notice('Either file is not exists or there are no content in file: @file', ['@file' => $file]);
      throw new NotFoundHttpException();
    }

    return new Response($output, Response::HTTP_OK, [
      'Content-type' => 'application/xml; charset=utf-8',
      'Cache-Control' => 'public, max-age=' . $this->configFactory->get('alshaya_feed.settings')->get('cache_time'),
    ]);
  }

}
