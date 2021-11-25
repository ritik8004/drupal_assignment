<?php

namespace Drupal\alshaya_egift_card\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_egift_card\Helper\EgiftCardHelper;
use Drupal\block_content\Entity\BlockContent;

/**
 * Alshaya Top Up Controller.
 */
class AlshayaTopUpController extends ControllerBase {

  /**
   * EgiftCardHelper.
   *
   * @var \Drupal\alshaya_egift_card\Helper\EgiftCardHelper
   */
  protected $egiftCardHelper;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The Alshaya Top Up Controller constructor.
   *
   * @param \Drupal\alshaya_egift_card\Helper\EgiftCardHelper $egiftCardHelper
   *   EgiftCardHelper.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   Entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(EgiftCardHelper $egiftCardHelper,
                              EntityTypeManagerInterface $entity_manager,
                              LanguageManagerInterface $language_manager) {
    $this->egiftCardHelper = $egiftCardHelper;
    $this->entityManager = $entity_manager;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_egift_card.egift_card_helper'),
      $container->get('entity_type.manager'),
      $container->get('language_manager')
    );
  }

  /**
   * Callback for opening the modal form.
   */
  public function topupcard() {
    $eGift_status = $this->egiftCardHelper->isEgiftCardEnabled();
    if (!$eGift_status) {
      return;
    }

    $lang = $this->languageManager->getCurrentLanguage()->getId();

    // Load the block object from block id.
    $block = $this->entityManager->getStorage('block')->load('alshayatopuptermsandconditions');
    // Render the block content view.
    $terms_block_content = $this->entityManager->getViewBuilder('block')->view($block, 'full', $lang);

    return [
      '#theme' => 'egift_topup_page',
      '#terms_block_content' => $terms_block_content,
    ];
  }

}
