<?php

namespace Drupal\alshaya_acm_promotion;

use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\node\Entity\Node;

/**
 * Class AlshayaPromotionsManager.
 *
 * @package Drupal\alshaya_acm_promotion
 */
class AlshayaPromotionsManager {

  /**
   * Entity Manager service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * Language Manager service.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * Entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Sku Manager service.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Cache Backend service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * AlshayaPromotionsManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Entity Manager service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger service.
   * @param \Drupal\Core\Language\LanguageManager $languageManager
   *   The language manager service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The Entity repository service.
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   The sku Manager service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LoggerChannelFactoryInterface $logger, LanguageManager $languageManager, EntityRepositoryInterface $entityRepository, SkuManager $skuManager, CacheBackendInterface $cache) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->logger = $logger;
    $this->languageManager = $languageManager;
    $this->entityRepository = $entityRepository;
    $this->skuManager = $skuManager;
    $this->cache = $cache;
  }

  /**
   * Helper function to fetch promotion node givern rule id.
   *
   * @param int $rule_id
   *   Rule id of the promotion to load.
   *
   * @return \Drupal\node\Entity\Node|null
   *   Return node if a promotion found associated with the rule id else Null.
   */
  public function getPromotionByRuleId($rule_id) {
    $query = $this->nodeStorage->getQuery();
    $query->condition('type', 'acq_promotion');
    $query->condition('field_acq_promotion_rule_id', $rule_id);
    $nids = $query->execute();

    if (empty($nids)) {
      return NULL;
    }
    else {
      // Log a message for admin to check errors in data.
      if (count($nids) > 1) {
        $this->logger->critical('Multiple nodes found for rule id @rule_id', ['@rule_id' => $rule_id]);
      }

      // We only load the first node.
      /* @var $node \Drupal\node\Entity\Node */
      $node = $this->nodeStorage->load(reset($nids));
      $langcode = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
      // Get the promotion with language fallback, if it did not have a
      // translation for $langcode.
      $node = $this->entityRepository->getTranslationFromContext($node, $langcode);
      return $node;
    }
  }

  /**
   * Helper function to fetch all promotions.
   */
  public function getAllPromotions() {
    $nodes = [];
    $query = $this->nodeStorage->getQuery();
    $nids = $query->condition('type', 'acq_promotion')->execute();
    if (!empty($nids)) {
      $nodes = Node::loadMultiple($nids);
    }

    return $nodes;
  }

  /**
   * Helper function to do a cheaper call to fetch skus for a promotion.
   *
   * @param \Drupal\node\Entity\Node $promotion
   *   Promotion for which we need to fetch skus.
   *
   * @return array
   *   List of skus related with a promotion.
   */
  public function getSkutextsForPromotion(Node $promotion) {
    if ($skus_cache = $this->cache->get('promotinos_sku_' . $promotion->id())) {
      $skus = $skus_cache->data;
    }
    else {
      $query = \Drupal::database()->select('acq_sku__field_acq_sku_promotions', 'fasp');
      $query->join('acq_sku_field_data', 'asfd', 'asfd.id = fasp.entity_id');
      $query->condition('fasp.field_acq_sku_promotions_target_id', $promotion->id());
      $query->condition('asfd.type', "configurable");
      $query->fields('asfd', ['id', 'sku']);
      $query->distinct();
      $config_skus = $query->execute()->fetchAllKeyed(0, 1);

      $query = \Drupal::database()->select('acq_sku__field_acq_sku_promotions', 'fasp');
      $query->join('acq_sku_field_data', 'asfd', 'asfd.id = fasp.entity_id');
      $query->condition('fasp.field_acq_sku_promotions_target_id', $promotion->id());
      $query->condition('asfd.type', "simple");
      $query->fields('asfd', ['id', 'sku']);
      $query->distinct();
      $simple_skus = $query->execute()->fetchAllKeyed(0, 1);

      $sku_tree = $this->skuManager->getSkuTree();
      $processed_sku_eids = [];

      foreach ($simple_skus as $sku) {
        if (isset($sku_tree[$sku])) {
          $parent_sku = $sku_tree[$sku];
          if (!in_array($parent_sku, $processed_sku_eids)) {
            $processed_sku_eids[] = $this->skuManager->getSkuTextFromId($parent_sku);;
          }
        }
      }

      $skus = array_unique(array_merge($processed_sku_eids, $config_skus));

      $this->cache->set('promotions_sku_' . $promotion->id(), $skus, Cache::PERMANENT, ['sku_list']);
    }

    return $skus;
  }

}
