<?php

namespace Drupal\acq_sku\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Language\LanguageInterface;

/**
 * Provides a list controller for acq_sku entity.
 *
 * @ingroup acq_sku
 */
class SKUListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = [];
    $build['table'] = parent::render();
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [];
    $header['id'] = $this->t('ID');
    $header['sku'] = $this->t('SKU');
    $header['title'] = $this->t('Name');
    $header['type'] = $this->t('Type');

    if (\Drupal::languageManager()->isMultilingual()) {
      $header['language_name'] = [
        'data' => $this->t('Language'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ];
    }

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = [];
    $languages = [];
    $mark = NULL;
    $row['id'] = $entity->id();
    $row['sku'] = $entity->getSKU();

    $langcode = $entity->language()->getId();

    $uri = $entity->toUrl();
    $options = $uri->getOptions();
    $options += ($langcode != LanguageInterface::LANGCODE_NOT_SPECIFIED && isset($languages[$langcode]) ? ['language' => $languages[$langcode]] : []);
    $uri->setOptions($options);
    $row['title']['data'] = [
      '#type' => 'link',
      '#title' => $entity->label(),
      '#suffix' => ' ' . render($mark),
      '#url' => $uri,
    ];

    $row['type'] = $entity->bundle();

    $language_manager = \Drupal::languageManager();
    if ($language_manager->isMultilingual()) {
      $row['language_name'] = $language_manager->getLanguageName($langcode);
    }

    return $row + parent::buildRow($entity);
  }

}
