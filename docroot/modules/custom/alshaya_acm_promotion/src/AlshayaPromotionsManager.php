<?php

namespace Drupal\alshaya_acm_promotion;

use Drupal\acq_promotion\AcqPromotionsManager;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
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
   * @param \Drupal\acq_promotion\AcqPromotionsManager $acq_promotions_manager
   *   Promotions manager service object from commerce code.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager,
                              LoggerChannelFactoryInterface $logger,
                              LanguageManager $languageManager,
                              EntityRepositoryInterface $entityRepository,
                              AcqPromotionsManager $acq_promotions_manager) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->logger = $logger->get('alshaya_acm_promotion');
    $this->languageManager = $languageManager;
    $this->entityRepository = $entityRepository;
    $this->acqPromotionsManager = $acq_promotions_manager;
  }

  /**
   * Helper function to fetch promotion node givern rule id.
   *
   * @param int $rule_id
   *   Rule id of the promotion to load.
   * @param string $rule_type
   *   Rule type of the promotion to load.
   *
   * @return \Drupal\node\Entity\Node|null
   *   Return node if a promotion found associated with the rule id else Null.
   */
  public function getPromotionByRuleId($rule_id, $rule_type = 'cart') {
    return $this->acqPromotionsManager->getPromotionByRuleId($rule_id, $rule_type);
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

}
