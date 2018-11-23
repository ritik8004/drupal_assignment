<?php

namespace Drupal\alshaya_paragraphs\Helper;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Class MigrateSymmetricToAsymmetric.
 *
 * @package Drupal\alshaya_paragraphs\Helper
 */
class MigrateSymmetricToAsymmetric {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Paragraph Storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $paragraphStorage;

  public static $fields = [
    'paragraph' => [
      'field_1_row_2_col_1',
      'field_1_row_2_col_2',
      'field_1_row_2_col',
      'field_1_row_promo_block',
      'field_1st_col_promo_block',
      'field_2nd_col_promo_block',
      'field_mobile_banner_image',
      'field_promo_block_button',
      'field_promo_block',
    ],
    'node' => [
      'field_promo_blocks',
      'field_banner',
      'field_delivery_banner',
      'field_promo_banner_full_width',
      'field_related_info',
      'field_slider',
    ],
    'taxonomy_term' => [
      'field_main_menu_highlight',
    ],
    'block_content' => [
      'field_paragraph_content',
    ],
  ];

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * MigrateSymmetricToAsymmetric constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Logger.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              LoggerChannelInterface $logger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;

    $this->paragraphStorage = $this->entityTypeManager->getStorage('paragraph');
  }

  /**
   * Migrate paragraphs for particular entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Node/Term/Block entity to migrate.
   */
  public function migrateEntity(EntityInterface $entity) {
    /** @var \Drupal\node\NodeInterface $entity */
    // Hard coded languages here, if we ever get three languages anything
    // below won't work.
    foreach (['en', 'ar'] as $langcode) {
      if (!($entity->hasTranslation($langcode))) {
        continue;
      }

      $entities[$langcode] = $entity->getTranslation($langcode);

      if ($entities[$langcode]->isDefaultTranslation()) {
        $defaultLangcode = $langcode;
      }
    }

    // There is only one translation, no need to go further.
    if (count($entities) < 2) {
      return;
    }

    /** @var \Drupal\node\NodeInterface $default */
    $default = $entities[$defaultLangcode];
    $translationLangcode = $defaultLangcode === 'en' ? 'ar' : 'en';
    $translation = $entities[$translationLangcode];

    $this->migrateContent($default, $translation, $defaultLangcode, $translationLangcode);
  }

  /**
   * Helper recursive function to migrate child entities.
   *
   * @param \Drupal\Core\Entity\EntityInterface $original
   *   Entity's default translation.
   * @param \Drupal\Core\Entity\EntityInterface $translation
   *   Entity's translation.
   * @param string $defaultLangcode
   *   Default translation language code.
   * @param string $translationLangcode
   *   Target translation language code.
   */
  private function migrateContent(EntityInterface $original, EntityInterface $translation, string $defaultLangcode, string $translationLangcode) {
    $this->logger->info('Migrating content for @type @id', [
      '@id' => $original->id(),
      '@type' => $original->getEntityTypeId(),
    ]);

    /** @var \Drupal\node\NodeInterface $entity */
    $fields = self::$fields[$original->getEntityTypeId()];

    foreach ($fields as $field) {
      if ($original->hasField($field) && $translation->hasField($field)) {
        $defaultValues = $original->get($field)->getValue();
        $translatedValues = $translation->get($field)->getValue();

        if (count($defaultValues) !== count($translatedValues)) {
          $this->logger->error('Content structure do not match for @type id: @id', [
            '@id' => $original->id(),
            '@type' => $original->getEntityTypeId(),
          ]);
        }

        $entities = [];
        $this->prepareBundleEntities($entities, $defaultValues, $defaultLangcode);
        $this->prepareBundleEntities($entities, $translatedValues, $translationLangcode);

        foreach ($entities as $bundleEntities) {
          foreach ($bundleEntities[$defaultLangcode] ?? [] as $index => $paragraph) {
            /** @var \Drupal\paragraphs\Entity\Paragraph $translatedParagraph */
            $translatedParagraph = $bundleEntities[$translationLangcode][$index] ?? NULL;
            if (empty($translatedParagraph)) {
              continue;
            }

            $this->migrateContent($paragraph, $translatedParagraph, $defaultLangcode, $translationLangcode);

            if ($paragraph->hasTranslation($translationLangcode)) {
              $paragraph->removeTranslation($translationLangcode);
            }

            $translatedParagraph = $this->getParagraph($translatedParagraph->getRevisionId(), $translationLangcode);
            $newTranslatedValues = $this->getTranslatedValues($translatedParagraph);
            $newTranslatedParagraph = $paragraph->addTranslation($translationLangcode, $newTranslatedValues);
            unset($newTranslatedParagraph->original);

            $this->logger->info('New Translated value: @value', [
              '@value' => json_encode($newTranslatedValues),
            ]);

            try {
              $newTranslatedParagraph->save();
            }
            catch (\Exception $e) {
              $this->logger->error('Error occurred while saving new translation for paragraph: @row. Message: @message', [
                '@row' => json_encode($newTranslatedValues),
                '@message' => $e->getMessage(),
              ]);
            }
          }
        }
      }
    }
  }

  /**
   * Get translated values from paragraph entity.
   *
   * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
   *   Entity to get translated values from.
   *
   * @return array
   *   Cleaned translated values array.
   */
  private function getTranslatedValues(Paragraph $paragraph): array {
    $translatedValues = $paragraph->toArray();

    // Remove unwanted values.
    $fields_to_remove = [
      'id',
      'uuid',
      'revision_id',
      'langcode',
      'type',
      'default_langcode',
      'revision_translation_affected',
      'content_translation_source',
      'content_translation_outdated',
      'content_translation_changed',
      'behavior_settings',
      'parent_id',
      'parent_type',
      'parent_field_name',
      'status',
      'revision_default',
      'uid',
      'revision_uid',
      'created',
    ];

    foreach ($fields_to_remove as $field_to_remove) {
      unset($translatedValues[$field_to_remove]);
    }

    return $translatedValues;
  }

  /**
   * Get fresh paragraph entity translated in requested language.
   *
   * @param mixed $revision_id
   *   Revision id.
   * @param string $langcode
   *   Language code.
   *
   * @return \Drupal\paragraphs\Entity\Paragraph|null
   *   Paragraph entity translated in requested language if found.
   */
  private function getParagraph($revision_id, string $langcode): ?Paragraph {
    $this->paragraphStorage->resetCache();
    $paragraph = $this->paragraphStorage->loadRevision($revision_id);
    if (!($paragraph instanceof Paragraph)) {
      return NULL;
    }

    if ($paragraph->hasTranslation($langcode)) {
      $paragraph = $paragraph->getTranslation($langcode);
    }

    return $paragraph;
  }

  /**
   * Prepare entities array grouped by bundle.
   *
   * @param array $entities
   *   Entities array - reference.
   * @param array $values
   *   Values to add to entities array.
   * @param string $langcode
   *   Language code.
   */
  private function prepareBundleEntities(array &$entities, array $values, string $langcode) {
    foreach ($values as $value) {
      $paragraph = $this->getParagraph($value['target_revision_id'], $langcode);

      if (!empty($paragraph)) {
        $bundle = $paragraph->bundle() == '1_row_1_col_dept'
          ? '1_row_1_col'
          : $paragraph->bundle();

        $entities[$bundle][$langcode][] = $paragraph;
      }
    }
  }

}
