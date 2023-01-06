<?php

namespace Drupal\alshaya_mobile_app\EventSubscriber;

use Drupal\alshaya_acm_product_category\Event\GetEnrichedCategoryDataEvent;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Alter the categories data.
 */
class CategoriesEnrichmentEventSubscriber implements EventSubscriberInterface {

  /**
   * Mobile app utility.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   *   Mobile app utility.
   */
  protected $mobileAppUtility;

  /**
   * Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Cacheability metadata.
   *
   * @var \Drupal\Core\Cache\CacheableMetadata
   */
  protected $cacheabilityMetadata;

  /**
   * The VID for the taxonomy we are targeting.
   */
  public const VOCABULARY_ID = 'acq_product_category';

  /**
   * Constructor for CategoriesEnrichmentEventSubscriber.
   *
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   Mobile app utility.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(
    MobileAppUtility $mobile_app_utility,
    Connection $connection,
    EntityTypeManagerInterface $entity_type_manager,
    LanguageManagerInterface $language_manager,
  ) {
    $this->mobileAppUtility = $mobile_app_utility;
    $this->connection = $connection;
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->cacheabilityMetadata = new CacheableMetadata();
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    return [
      GetEnrichedCategoryDataEvent::EVENT_NAME => [
        ['onGetEnrichedCategoryData', 0],
      ],
    ];
  }

  /**
   * Alter the term data in the event.
   *
   * @param \Drupal\alshaya_acm_product_category\Event\GetEnrichedCategoryDataEvent $event
   *   Contains term data to alter.
   */
  public function onGetEnrichedCategoryData(GetEnrichedCategoryDataEvent $event) {
    // Do not process further if event data is already set.
    if (!empty($event->getData())) {
      return;
    }
    $event->setData($this->getCategoryEnrichmentData($event->getLangcode()));
    $event->setCacheabilityMetadata($this->cacheabilityMetadata);
  }

  /**
   * Get the category enrichment data.
   *
   * @param string $langcode
   *   Langcode value.
   *
   * @return array
   *   Enrichment data.
   */
  public function getCategoryEnrichmentData($langcode) {
    $tids = $this->getEnrichedTerms($langcode);
    $data = [];
    foreach ($tids as $tid) {
      $term_data = $this->getEnrichedTermData($tid);
      $data[key($term_data)] = $term_data[key($term_data)];
    }

    $this->cacheabilityMetadata->addCacheTags(['taxonomy_term_list:' . self::VOCABULARY_ID]);
    return $data;
  }

  /**
   * Get the list of tids for which we have enrichment.
   *
   * @return array
   *   Array of tids.
   */
  protected function getEnrichedTerms($langcode) {
    $query = $this->connection->select('taxonomy_term_field_data', 'tfd');
    $query->fields('tfd', ['tid']);

    // For the `Term background color`.
    $query->leftJoin('taxonomy_term__field_term_background_color', 'ttbc', 'ttbc.entity_id = tfd.tid AND ttbc.langcode = tfd.langcode');
    // For the `Term font color`.
    $query->leftJoin('taxonomy_term__field_term_font_color', 'ttfc', 'ttfc.entity_id = tfd.tid AND ttfc.langcode = tfd.langcode');

    // For the `Term icon`.
    $query->leftJoin('taxonomy_term__field_icon', 'ttic', 'ttic.entity_id = tfd.tid');

    // For the `Include in desktop`.
    $query->leftJoin('taxonomy_term__field_include_in_desktop', 'in_desktop', 'in_desktop.entity_id = tfd.tid');

    // For the `Include in mobile`.
    $query->leftJoin('taxonomy_term__field_include_in_mobile_tablet', 'in_mobile', 'in_mobile.entity_id = tfd.tid');

    // For the `move to right`.
    $query->leftJoin('taxonomy_term__field_move_to_right', 'mtr', 'mtr.entity_id = tfd.tid');

    // For the `Overridden target link`.
    $query->leftJoin('taxonomy_term__field_target_link', 'tttl', 'tttl.entity_id = tfd.tid');

    // For the `Override target link flag`.
    $query->leftJoin('taxonomy_term__field_override_target_link', 'ttotl', 'ttotl.entity_id = tfd.tid');

    // For the `Highlights paragraphs`.
    $query->leftJoin('taxonomy_term__field_main_menu_highlight', 'ttmmh', 'ttmmh.entity_id = tfd.tid');

    // For the `Remove term in breadcrumb`.
    $query->leftJoin('taxonomy_term__field_remove_term_in_breadcrumb', 'ttrtb', 'ttrtb.entity_id = tfd.tid');

    // For the `Display as clickable link`.
    $query->leftJoin('taxonomy_term__field_display_as_clickable_link', 'ttdacl', 'ttdacl.entity_id = tfd.tid');

    // Create a OR condition group, so if any of the above fields has
    // an overridden values, we need to fetch and clone them.
    $orCondGroup = $query->orConditionGroup();
    $orCondGroup->isNotNull('ttbc.field_term_background_color_value');
    $orCondGroup->isNotNull('ttfc.field_term_font_color_value');
    $orCondGroup->isNotNull('ttic.field_icon_target_id');
    $orCondGroup->isNotNull('tttl.field_target_link_uri');
    $orCondGroup->isNotNull('ttmmh.field_main_menu_highlight_target_id');
    $orCondGroup->condition('in_desktop.field_include_in_desktop_value', '0');
    $orCondGroup->condition('in_mobile.field_include_in_mobile_tablet_value', '0');
    $orCondGroup->condition('mtr.field_move_to_right_value', '1');
    $orCondGroup->condition('ttotl.field_override_target_link_value', '1');
    $orCondGroup->condition('ttrtb.field_remove_term_in_breadcrumb_value', '1');
    $orCondGroup->condition('ttdacl.field_display_as_clickable_link_value', '0');
    $query->condition($orCondGroup);

    $query->condition('tfd.langcode', $langcode);
    $query->condition('tfd.vid', 'acq_product_category');

    // Get the terms satisfying the above conditions.
    return $query->distinct()->execute()->fetchAllKeyed(0, 0);
  }

  /**
   * Enriches the category term fields.
   *
   * @param int $tid
   *   Term id.
   */
  public function getEnrichedTermData($tid) {
    /** @var \Drupal\taxonomy\Entity\Term $term */
    $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid);
    $current_langcode = $this->languageManager->getCurrentLanguage()->getId();
    $term_url = $term->toUrl()->toString(TRUE)->getGeneratedUrl();
    // Trim slashes and remove langcode.
    $term_url = preg_replace('/' . $current_langcode . '\//', '', trim($term_url, '/'), 1);
    $data = [
      'id' => $term->id(),
      'name' => $term->label(),
      'include_in_desktop' => (int) $term->get('field_include_in_desktop')->getString(),
      'include_in_mobile_tablet' => (int) $term->get('field_include_in_mobile_tablet')->getString(),
      'move_to_right' => (int) $term->get('field_move_to_right')->getString(),
      'font_color' => $term->get('field_term_font_color')->getString(),
      'background_color' => $term->get('field_term_background_color')->getString(),
      'remove_from_breadcrumb' => (int) $term->get('field_remove_term_in_breadcrumb')->getString(),
      'item_clickable' => (bool) $term->get('field_display_as_clickable_link')->getString(),
      'deeplink' => $this->mobileAppUtility->getDeepLink($term),
    ];

    $this->cacheabilityMetadata->addCacheableDependency($term);

    return [$term_url => $data];
  }

}
