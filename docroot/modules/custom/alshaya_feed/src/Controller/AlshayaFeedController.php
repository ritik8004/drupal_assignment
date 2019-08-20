<?php

namespace Drupal\alshaya_feed\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Language\LanguageManagerInterface;
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
   * AlshayaFeedController constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(LanguageManagerInterface $language_manager) {
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('language_manager')
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
    $output = file_get_contents(ltrim($file, '/'));
    if (!$output) {
      throw new NotFoundHttpException();
    }

    return new Response($output, Response::HTTP_OK, [
      'Content-type' => 'application/xml; charset=utf-8',
      'X-Robots-Tag' => 'noindex, follow',
    ]);
  }

}
