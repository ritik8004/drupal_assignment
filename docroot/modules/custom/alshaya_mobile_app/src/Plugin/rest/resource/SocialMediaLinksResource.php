<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class Social Media Links Resource.
 *
 * @RestResource(
 *   id = "social_media_links",
 *   label = @Translation("Social media links"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/social-media-links",
 *   }
 * )
 */
class SocialMediaLinksResource extends ResourceBase {

  /**
   * Array of content for dependency.
   *
   * @var array
   */
  protected $content = [];

  /**
   * Menu name.
   */
  public const MENU_NAME = 'social-links';

  /**
   * Menu class pattern that needs to be replaced.
   */
  public const MENU_CLASS_PATTERN = 'c-social-links--';

  /**
   * Menu link tree.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuLinkTree;

  /**
   * Entity Type Manager service object.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * SocialMediaLinksResource constructor.
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
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_link_tree
   *   Menu link tree.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager service object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, MenuLinkTreeInterface $menu_link_tree, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->menuLinkTree = $menu_link_tree;
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('menu.link_tree'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Responds to GET requests.
   *
   * Returns available social method links.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing social methods data.
   */
  public function get() {
    $response_data = [];
    // Get all menu link tree elements.
    $menu_link_tree_elements = $this->menuLinkTree->load(self::MENU_NAME, new MenuTreeParameters());
    if (!empty($menu_link_tree_elements)) {
      foreach ($menu_link_tree_elements as $menu_link_tree_element) {
        $menu_link_content = $menu_link_tree_element->link;

        // If menu is not enabled, skip it.
        if (!$menu_link_content->isEnabled()) {
          continue;
        }

        // Get class of the menu item.
        $menu_class = '';
        $menu_plugin = $menu_link_content->getPluginDefinition();
        if (isset($menu_plugin['options']['attributes']['class']) && !empty($menu_plugin['options']['attributes']['class'])) {
          $menu_class = $menu_plugin['options']['attributes']['class'];
          $menu_class = is_array($menu_class) ? reset($menu_class) : $menu_class;
        }
        // Get menu id.
        $menu_id = $menu_plugin['metadata']['entity_id'];
        // Load with menu id and get menu link object.
        $menu_link_obj = $this->entityTypeManager->getStorage('menu_link_content')->load($menu_id);

        $response_data[] = [
          'name' => $menu_link_content->getTitle(),
          'media' => !empty($menu_class) ? str_replace(self::MENU_CLASS_PATTERN, '', $menu_class) : '',
          'url' => $menu_link_content->getUrlObject()->toString(TRUE)->getGeneratedUrl(),
          'social_id' => !empty($menu_link_obj->social_id->value) ? $menu_link_obj->social_id->value : NULL,
        ];

        // Adding to property for using later to attach cacheable dependency.
        $this->content[] = $menu_link_obj;
      }

      $response = new ResourceResponse($response_data);
      $this->addCacheableDependency($response);
      return $response;
    }

    // Sending modified response so response is not cached when no social media
    // menu item available.
    return (new ModifiedResourceResponse($response_data));
  }

  /**
   * Adding content dependency to the response.
   *
   * @param \Drupal\rest\ResourceResponse $response
   *   Response object.
   */
  protected function addCacheableDependency(ResourceResponse $response) {
    if (!empty($this->content)) {
      foreach ($this->content as $content) {
        $response->addCacheableDependency($content);
      }
    }
  }

}
