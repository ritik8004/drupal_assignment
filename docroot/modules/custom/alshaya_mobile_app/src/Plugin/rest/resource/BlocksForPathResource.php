<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\block\Entity\Block;
use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\block\BlockRepository;
use Symfony\Component\HttpFoundation\Request;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\path_alias\AliasRepositoryInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Language\LanguageManagerInterface;

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
   * Block repository.
   *
   * @var \Drupal\block\BlockRepository
   */
  protected $blockRepository;

  /**
   * Mobile app utility.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   */
  protected $mobileAppUtility;

  /**
   * Contains cacheable entities.
   *
   * @var array
   */
  protected $cacheableEntities = [];

  /**
   * The alias storage.
   *
   * @var \Drupal\path_alias\AliasRepositoryInterface
   */
  protected $aliasStorage;

  /**
   * The Path Validator service.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

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
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\block\BlockRepository $block_respository
   *   Block repository.
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   The mobile app utility service.
   * @param \Drupal\path_alias\AliasRepositoryInterface $alias_storage
   *   The alias storage service.
   * @param \Drupal\Core\Path\PathValidatorInterface $pathValidator
   *   The path validator service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    RequestStack $request_stack,
    ConfigFactoryInterface $config_factory,
    EntityRepositoryInterface $entity_repository,
    BlockRepository $block_respository,
    MobileAppUtility $mobile_app_utility,
    AliasRepositoryInterface $alias_storage,
    PathValidatorInterface $pathValidator,
    LanguageManagerInterface $language_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->requestStack = $request_stack;
    $this->configFactory = $config_factory;
    $this->entityRepository = $entity_repository;
    $this->blockRepository = $block_respository;
    $this->mobileAppUtility = $mobile_app_utility;
    $this->aliasStorage = $alias_storage;
    $this->pathValidator = $pathValidator;
    $this->languageManager = $language_manager;
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
      $container->get('request_stack'),
      $container->get('config.factory'),
      $container->get('entity.repository'),
      $container->get('block.repository'),
      $container->get('alshaya_mobile_app.utility'),
      $container->get('path_alias.repository'),
      $container->get('path.validator'),
      $container->get('language_manager')
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

    if (empty($alias)
      || (!($this->pathValidator->isValid($alias))
      && !($this->aliasStorage->lookupByAlias('/' . $alias, $this->languageManager->getCurrentLanguage()->getId())))
      ) {
      $this->mobileAppUtility->throwException();
    }

    // Create a fake request for the url in api request.
    $request = Request::create($alias);
    $this->requestStack->push($request);

    $response_data = [];

    // Get all visible blocks.
    $region_blocks = $this->blockRepository->getVisibleBlocksPerRegion();

    // If region is present in query parameter then
    // only return blocks of that region.
    $region = $currentRequest->query->get('region');

    if ($region !== NULL) {
      if (!array_key_exists($region, $region_blocks)) {
        $this->mobileAppUtility->throwException();
      }
      $region_blocks = [$region => $region_blocks[$region]];
    }

    foreach ($region_blocks as $blocks) {
      foreach ($blocks as $block) {
        if ($block instanceof Block) {
          $plugin_id = $block->getPluginId();
          if (!str_contains($plugin_id, 'block_content:')) {
            continue;
          }

          $block_uuid = str_replace('block_content:', '', $plugin_id);
          // Load content block based on block info.
          $content_block = $this->entityRepository->loadEntityByUuid('block_content', $block_uuid);
          if ($content_block) {
            $content_block = $this->entityRepository->getTranslationFromContext($content_block);
            // Getting the first input value of the body field.
            $body_value = $content_block->get('body')->first();
            $response_data[$block->id()] = [
              'title' => $content_block->label(),
              'body' => !empty($body_value) ? $body_value->getValue()['value'] : NULL,
              'type' => 'custom',
            ];
          }
        }
      }

      $this->cacheableEntities[] = $block;
    }

    // Remove the request we added to work with core.
    $this->requestStack->pop();

    $response = new ResourceResponse($response_data);
    foreach ($this->cacheableEntities as $cacheable_entity) {
      $response->addCacheableDependency($cacheable_entity);
    }

    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
      '#cache' => [
        'contexts' => [
          'url.query_args:page',
          'url.query_args:url',
          'url.query_args:region',
        ],
        'tags' => ['config:block_list'],
      ],
    ]));

    return $response;
  }

}
