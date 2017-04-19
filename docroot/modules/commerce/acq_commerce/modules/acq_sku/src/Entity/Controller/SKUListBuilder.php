<?php

namespace Drupal\acq_sku\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

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
    $build['table'] = parent::render();
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['sku'] = $this->t('SKU');
    $header['name'] = $this->t('Name');
    $header['price'] = $this->t('Price');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['id'] = $entity->id();
    $row['sku'] = $entity->getSKU();
    $row['name'] = $entity->link();
    $row['price'] = $entity->price->value;

    return $row + parent::buildRow($entity);
  }

}
