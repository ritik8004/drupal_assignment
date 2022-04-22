<?php

namespace Drupal\acq_sku\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the SKU type entity.
 *
 * @ConfigEntityType(
 *   id = "acq_sku_type",
 *   label = @Translation("SKU type"),
 *   handlers = {
 *     "list_builder" = "Drupal\acq_sku\SKUTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\acq_sku\Form\SKUTypeForm",
 *       "edit" = "Drupal\acq_sku\Form\SKUTypeForm",
 *       "delete" = "Drupal\acq_sku\Form\SKUTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\acq_sku\SKUTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "type",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/config/sku/{acq_sku_type}",
 *     "add-form" = "/admin/commerce/config/sku/add",
 *     "edit-form" = "/admin/commerce/config/sku/{acq_sku_type}",
 *     "delete-form" = "/admin/commerce/config/sku/{acq_sku_type}/delete",
 *     "collection" = "/admin/commerce/config/sku"
 *   },
 *   bundle_of = "acq_sku"
 * )
 */
class SKUType extends ConfigEntityBundleBase implements SKUTypeInterface {

  /**
   * The Product type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Product type label.
   *
   * @var string
   */
  protected $label;

}
