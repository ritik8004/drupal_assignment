<?php

namespace Drupal\alshaya_options_list\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\alshaya_options_list\AlshayaOptionsListHelper;

/**
 * Controller to add options list page.
 */
class AlshayaOptionsPageController extends ControllerBase {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Request stack.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * Alshaya Options List Service.
   *
   * @var Drupal\alshaya_options_list\AlshayaOptionsListHelper
   */
  protected $alshayaOptionsService;

  /**
   * AlshayaOptionsPageController constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   * @param Drupal\alshaya_options_list\AlshayaOptionsListHelper $alshaya_options_service
   *   Alshaya options service.
   */
  public function __construct(LanguageManagerInterface $language_manager,
                              RequestStack $request_stack,
                              AlshayaOptionsListHelper $alshaya_options_service) {
    $this->languageManager = $language_manager;
    $this->requestStack = $request_stack;
    $this->alshayaOptionsService = $alshaya_options_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('language_manager'),
      $container->get('request_stack'),
      $container->get('alshaya_options_list.alshaya_options_service')
    );
  }

  /**
   * Returns the build for options page.
   *
   * @return array
   *   Build array.
   */
  public function optionsPage() {
    if (!$this->alshayaOptionsService->optionsPageEnabled()) {
      throw new NotFoundHttpException();
    }

    $libraries = [
      'alshaya_white_label/optionlist_filter',
      'alshaya_options_list/alshaya_options_list_search',
    ];
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    // Get current request uri.
    $request = $this->requestStack->getCurrentRequest()->getRequestUri();
    // Remove query parameters.
    $request = explode('?', $request);
    // Remove langcode.
    $request = str_replace('/' . $langcode . '/', '', $request[0]);

    // Get the options data to display the option page.
    $options_list = $this->alshayaOptionsService->getOptionsList($request);

    $options_list = [
      '#theme' => 'alshaya_options_page',
      '#options_list' => $options_list['options_list'],
      '#page_title' => $options_list['title'],
      '#description' => $options_list['description'],
      '#attached' => [
        'library' => $libraries,
      ],
      '#cache' => [
        'tags' => $options_list['cache_tags'],
      ],
    ];

    return $options_list;
  }

}
