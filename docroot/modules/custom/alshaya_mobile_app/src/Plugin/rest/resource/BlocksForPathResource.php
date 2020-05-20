<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Executable\ExecutableManagerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a resource to get all blocks for given path.
 *
 * @RestResource(
 *   id = "blocks_for_path",
 *   label = @Translation("Blocks for path."),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/blocks"
 *   }
 * )
 */
class BlocksForPathResource extends ResourceBase {

  use StringTranslationTrait;

  /**
   * The mobile app utility service.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   */
  protected $mobileAppUtility;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The condition manager.
   *
   * @var \Drupal\Core\Executable\ExecutableManagerInterface
   */
  protected $conditionManager;

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * BlocksForPathResource constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param array $serializer_formats
   *   Serializer formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger channel.
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   The mobile app utility service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param Drupal\Core\Executable\ExecutableManagerInterface $condition_manager
   *   The condition manager.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    MobileAppUtility $mobile_app_utility,
    RequestStack $request_stack,
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory,
    EntityRepositoryInterface $entity_repository,
    LanguageManagerInterface $language_manager,
    ExecutableManagerInterface $condition_manager,
    CurrentPathStack $current_path
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->mobileAppUtility = $mobile_app_utility;
    $this->requestStack = $request_stack;
    $this->configFactory = $config_factory;
    $this->blockStorage = $entity_type_manager->getStorage('block');
    $this->entityRepository = $entity_repository;
    $this->languageManager = $language_manager;
    $this->conditionManager = $condition_manager;
    $this->currentPath = $current_path;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('alshaya_mobile_app'),
      $container->get('alshaya_mobile_app.utility'),
      $container->get('request_stack'),
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('entity.repository'),
      $container->get('language_manager'),
      $container->get('plugin.manager.condition'),
      $container->get('path.current')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing list custom blocks.
   */
  public function get() {
    $currentRequest = $this->requestStack->getCurrentRequest();
    // Path alias of page.
    $alias = $currentRequest->query->get('url');
    if (!$alias) {
      $page = $currentRequest->query->get('page');
      $alias = $this->configFactory->get('alshaya_mobile_app.settings')->get('static_page_mappings.' . $page);
    }

    if (empty($alias)) {
      $this->mobileAppUtility->throwException();
    }

    $eligibleBlocks = [];
    $currentTheme = $this->configFactory->get('system.theme')->get('default');
    $currentLanguage = $this->languageManager->getCurrentLanguage()->getId();

    $contentBlockIds = \Drupal::entityQuery('block_content')->execute();

    foreach ($contentBlockIds ?? [] as $id) {
      $block = BlockContent::load($id);
      $blockInstances = $this->blockStorage->loadByProperties([
        'plugin' => 'block_content:' . $block->get('uuid')->getString(),
        'status' => 1,
        'theme' => $currentTheme,
      ]);

      foreach ($blockInstances ?? [] as $instanceId => $blockInstance) {
        $blockVisibility = TRUE;
        $visibility = $blockInstance->getVisibility();

        foreach ($visibility ?? [] as $key => $value) {
          switch ($key) {
            case 'request_path':
              if (!$blockVisibility) {
                continue;
              }

              /* @var \Drupal\system\Plugin\Condition\RequestPath $condition */
              $condition = $this->conditionManager->createInstance('request_path');
              $condition->setConfiguration($value);

              // Pushing path from API query parameter to request stack to check
              // block visibility for that path instead of the current path.
              $request = Request::create($alias);
              $this->requestStack->push($request);

              $pathMatch = $condition->evaluate();
              $negate = $value['negate'];

              if (($pathMatch && $negate) || (!$pathMatch && !$negate)) {
                $blockVisibility = FALSE;
              }
              $this->requestStack->pop();

              break;

            case 'entity_bundle:node':
              $node = $this->mobileAppUtility->getNodeFromAlias($alias);
              if (!$blockVisibility && !($node instanceof NodeInterface)) {
                continue;
              }

              if (!$this->checkBlockVisibilityCondition('node_type', $value, 'node', $node)) {
                $blockVisibility = FALSE;
              }
              break;

            case 'language':
              if (!$blockVisibility) {
                continue;
              }

              if (!$this->checkBlockVisibilityCondition('language', $value, 'language', $currentLanguage)) {
                $blockVisibility = FALSE;
              }
              break;
          }
        }
        if ($blockVisibility) {
          // Load block translation if current language is different from
          // block default language and tranlation exists.
          $block = (($block->get('langcode')->getString() !== $currentLanguage)
          && ($block->hasTranslation($currentLanguage)))
          ? $block->getTranslation($currentLanguage)
          : $block;

          $eligibleBlocks[$instanceId] = [
            'title' => $block->label(),
            'body' => $block->body->value,
            'type' => $this->t('Custom'),
          ];
        }
      }
    }

    $response_data['blocks'] = $eligibleBlocks;

    $response = new ResourceResponse($response_data);
    $response->addCacheableDependency($node);
    foreach ($this->mobileAppUtility->getCacheableEntities() as $cacheable_entity) {
      $response->addCacheableDependency($cacheable_entity);
    }

    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
      '#cache' => [
        'contexts' => [
          'url.query_args:page',
          'url.query_args:url',
        ],
        'tags' => array_merge(['config:block_list'], $this->mobileAppUtility->getBlockCacheTags()),
      ],
    ]));

    return $response;
  }

  /**
   * Check block visibility conditions.
   *
   * @param string $condition_key
   *   Visibility condition key.
   * @param mixed $condition_value
   *   Condition value.
   * @param string $context_key
   *   Context key.
   * @param mixed $context_value
   *   Context value.
   *
   * @return bool
   *   TRUE/FALSE to show if condition is matched or not.
   */
  public function checkBlockVisibilityCondition($condition_key, $condition_value, $context_key, $context_value) {
    /* @var \Drupal\system\Plugin\Condition\RequestPath $condition */
    $condition = $this->conditionManager->createInstance($condition_key)
      ->setConfiguration($condition_value)
      ->setContextValue($context_key, $context_value);

    $condition_match = $condition->evaluate();

    return $condition_match;
  }

}
