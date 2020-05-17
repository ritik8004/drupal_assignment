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
    LanguageManagerInterface $language_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->mobileAppUtility = $mobile_app_utility;
    $this->requestStack = $request_stack->getCurrentRequest();
    $this->configFactory = $config_factory;
    $this->blockStorage = $entity_type_manager->getStorage('block');
    $this->entityRepository = $entity_repository;
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
      $container->get('alshaya_mobile_app.utility'),
      $container->get('request_stack'),
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('entity.repository'),
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
    // Path alias of page.
    $alias = $this->requestStack->query->get('url');
    if (!$alias) {
      $page = $this->requestStack->query->get('page');
      $alias = $this->configFactory->get('alshaya_mobile_app.settings')->get('static_page_mappings.' . $page);
    }

    if (empty($alias)) {
      $this->mobileAppUtility->throwException();
    }

    $eligibleBlocks = [];
    $node = $this->mobileAppUtility->getNodeFromAlias($alias);
    $currentBundle = ($node instanceof NodeInterface) ? $node->bundle() : '';
    $currentTheme = $this->configFactory->get('system.theme')->get('default');
    $currentLanguage = $this->languageManager->getCurrentLanguage()->getId();

    $contentBlockIds = \Drupal::entityQuery('block_content')->execute();

    foreach ($contentBlockIds ?? [] as $id) {
      $block = BlockContent::load($id);
      // Load block translation if current language is different from
      // block default language and tranlation exists.
      $block = (($block->get('langcode')->getString() !== $currentLanguage)
        && ($block->hasTranslation($currentLanguage)))
        ? $block->getTranslation($currentLanguage)
        : $block;
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
              $pages = $value['pages'];
              $negate = $value['negate'];

              // Replace <front> with front page url alias.
              $frontPageAlias = $this->configFactory->get('alshaya_mobile_app.settings')->get('static_page_mappings.front');
              $pages = str_replace('<front>', $frontPageAlias, $pages);

              if (((stripos($pages, $alias) !== FALSE) && $negate === TRUE)
                || ((stripos($pages, $alias) === FALSE) && $negate === FALSE)) {
                $blockVisibility = FALSE;
                continue;
              }
              break;

            case 'entity_bundle:node':
              if (!$blockVisibility && empty($currentBundle)) {
                continue;
              }
              $bundles = $value['bundles'];
              $negate = $value['negate'];

              if ((!in_array($currentBundle, $bundles) && $negate === FALSE)
                || (in_array($currentBundle, $bundles) && $negate === TRUE)) {
                $blockVisibility = FALSE;
                continue;
              }
              break;

            case 'language':
              if (!$blockVisibility) {
                continue;
              }
              $langcodes = $value['langcodes'];
              $negate = $value['negate'];

              // If current language in not in the list of visibility langcode
              // and the block id already added in eligibleBlocks list then
              // remove the current block from the list or else continue.
              if ((!in_array($currentLanguage, $langcodes) && $negate === FALSE)
                || (in_array($currentLanguage, $langcodes) && $negate === TRUE)) {
                $blockVisibility = FALSE;
                continue;
              }
              break;
          }
        }
        if ($blockVisibility) {
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
        'tags' => array_merge([], $this->mobileAppUtility->getBlockCacheTags()),
      ],
    ]));

    return $response;
  }

}
