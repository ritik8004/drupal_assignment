<?php

namespace Drupal\alshaya_acm_product\Controller;

use Drupal\acq_sku\Entity\SKU;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ProductStockController.
 *
 * @package Drupal\alshaya_acm_product\Controller
 */
class ProductStockController extends ControllerBase {

  /**
   * Controller to check for Product's availability.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity related to SKU for which we checking the stock.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
   *   Response object returning availability.
   */
  public function checkStock(EntityInterface $entity) {
    if ($entity instanceof Node) {
      $sku = $entity->get('field_skus')->first()->getString();
      $sku_entity = SKU::loadFromSku($sku);
    }
    elseif ($entity instanceof SKU) {
      $sku_entity = $entity;
    }
    $sku_code = $sku_entity->getSku();

    $max_quantity = alshaya_acm_is_product_in_stock($sku_entity);
    if (!$max_quantity) {
      $build['#markup'] = '<span>' . $this->t('out of stock') . '</span>';
      $response['html'] = \Drupal::service('renderer')->render($build);
      $response['max_quantity'] = 0;
    }
    else {
      if ($entity instanceof SKU) {
        $settings = '';
        $build = $this->fetchAddCartForm($sku_entity);
        $rendered_form = \Drupal::service('renderer')->renderRoot($build);

        // Get the data from BubbleMetaData.
        $data = BubbleableMetadata::createFromRenderArray($build);
        // Retrieve the attachments from the $data.
        $attachments = $data->getAttachments();

        // Generate the settings to be sent back with the ajax response.
        if (!empty($attachments['drupalSettings'])) {
          $settings .= '<script type="text/javascript">jQuery.extend(drupalSettings, ';
          $settings .= Json::encode($attachments['drupalSettings']);
          $settings .= ');</script>';
        }

        $response['html'] = $rendered_form . $settings;
      }
      else {
        $build['#markup'] = '';
        $response['html'] = \Drupal::service('renderer')->render($build);
      }
      $response['max_quantity'] = $max_quantity;
    }

    return new JsonResponse($response);
  }

  /**
   * Helper function to build the add cart form.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   Sku Entity for which we building the form.
   *
   * @return mixed
   *   Add to cart form for the sku.
   */
  protected function fetchAddCartForm(SKU $sku_entity) {
    $plugin_manager = \Drupal::service('plugin.manager.sku');
    $plugin_definition = $plugin_manager->pluginFromSKU($sku_entity);

    $class = $plugin_definition['class'];
    $plugin = new $class();

    $form['add_to_cart'] = \Drupal::service('acq_sku.form_builder')->getForm($plugin, $sku_entity);
    $form['add_to_cart']['#weight'] = 50;

    $form['add_to_cart']['add_to_cart']['#ajax']['url'] = new Url('entity.node.canonical', ['node' => 67]);
    $form['add_to_cart']['add_to_cart']['#ajax']['options']['query'][FormBuilderInterface::AJAX_FORM_REQUEST] = TRUE;
    return $form;
  }

}
