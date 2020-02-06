<?php

namespace Drupal\alshaya_master\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Class AlshayaEntityHelper.
 *
 * @package Drupal\alshaya_master\Service
 */
class AlshayaEntityHelper {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * AlshayaEntityHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language Manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              LanguageManagerInterface $language_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
  }

  /**
   * Helper function to entity label in site's default language.
   *
   * @param string $entity_type
   *   Entity Type.
   * @param mixed $entity_id
   *   Entity Id, at times it is sent as string.
   *
   * @return string
   *   Label of entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getLabelInSiteDefaultLanguage(string $entity_type, $entity_id) {
    $storage = $this->entityTypeManager->getStorage($entity_type);
    $entity = $storage->load($entity_id);

    if (empty($entity)) {
      return '';
    }

    $default_lang_code = $this->languageManager->getDefaultLanguage()->getId();

    if ($entity->language()->getId() != $default_lang_code
      && method_exists($entity, 'hasTranslation')
      && $entity->hasTranslation($default_lang_code)) {
      $entity = $entity->getTranslation($default_lang_code);
    }

    return $entity->label();
  }

  /**
   * Query to get the product nodes.
   *
   * @return \Drupal\Core\Database\Query\AlterableInterface
   *   Return a query object.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getNodesQuery() {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    return $query->condition('type', 'acq_product')
      ->condition('status', NodeInterface::PUBLISHED)
      ->addTag('get_display_node_for_sku');
  }

}
