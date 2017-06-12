<?php

namespace Drupal\acq_sku\AcquiaCommerce;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Language\LanguageInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Link;

/**
 * Defines a base SKU Plugin. Can be used as a template for a new SKU type.
 */
abstract class SKUPluginBase implements SKUPluginInterface, FormInterface {

  /**
   * {@inheritdoc}
   *
   * If you need to alter the display of the whole entity, override this method.
   */
  public function build(array $build) {
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sku_base_form';
  }

  /**
   * {@inheritdoc}
   *
   * If you need more than one form in your SKU Type, separate out the forms
   * using form arguments. By default we fetch the SKU from the form state and
   * render the addToCartForm.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $build_info = $form_state->getBuildInfo();

    if (empty($build_info['args'])) {
      return $this->addToCartForm($form, $form_state);
    }

    $sku = $build_info['args'][0];

    if (get_class($sku) == 'Drupal\acq_sku\Entity\SKU') {
      return $this->addToCartForm($form, $form_state, $build_info['args'][0]);
    }

    return $this->addToCartForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * If you need more than one form validation in your SKU Type, separate out
   * the form validation using form arguments.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->addToCartValidate($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * If you need more than one form submission in your SKU Type, separate out
   * the form validation using form arguments.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->addToCartSubmit($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function addToCartForm(array $form, FormStateInterface $form_state, SKU $sku = NULL) {

  }

  /**
   * {@inheritdoc}
   */
  public function addToCartValidate(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function addToCartSubmit(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function processImport($sku, array $product) {

  }

  /**
   * {@inheritdoc}
   */
  public function cartName($sku, array $cart) {
    // For all configurable products we will have sku of simple variant only
    // in cart so we add a check if parent is available, process cartName of
    // that.
    if ($parent_sku = $this->getParentSku($sku)) {
      $plugin_manager = \Drupal::service('plugin.manager.sku');
      $plugin = $plugin_manager->pluginInstanceFromType($parent_sku->bundle());
      return $plugin->cartName($sku, $cart);
    }

    $label = $sku->label();
    $display_node = $this->getDisplayNode($sku);
    $url = $display_node->toUrl();
    $renderArray = Link::fromTextAndUrl($label, $url)->toRenderable();
    return render($renderArray);
  }

  /**
   * Get parent of current product.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Current product.
   *
   * @return \Drupal\acq_sku\Entity\SKU|null
   *   Parent product or null if not found.
   */
  public function getParentSku(SKU $sku) {
    $query = \Drupal::database()->select('acq_sku', 'acq_sku');
    $query->addField('acq_sku', 'sku');
    $query->join('acq_sku__field_configured_skus', 'child_sku', 'acq_sku.id = child_sku.entity_id');
    $query->condition('child_sku.field_configured_skus_value', $sku->getSku());

    if (\Drupal::languageManager()->isMultilingual()) {
      $query->condition('acq_sku.langcode', $sku->get('langcode')->getString());
    }

    $parent_sku = $query->execute()->fetchField();

    if (empty($parent_sku)) {
      return NULL;
    }

    return SKU::loadFromSku($parent_sku);
  }

  /**
   * {@inheritdoc}
   */
  public function getDisplayNode(SKU $sku, $check_parent = TRUE, $create_translation = FALSE) {
    if ($check_parent) {
      if ($parent_sku = $this->getParentSku($sku)) {
        $sku = $parent_sku;
      }
    }

    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'acq_product');
    $query->condition('field_skus', $sku->getSku());

    $query->range(0, 1);

    $result = $query->execute();

    if (empty($result)) {
      return NULL;
    }

    $nid = reset($result);
    $node = Node::load($nid);

    // Check language checks if site is in multilingual mode.
    if (\Drupal::languageManager()->isMultilingual()) {
      // If language of SKU and node are the same, we return the node.
      if ($node->language()->getId() == $sku->get('langcode')->getString()) {
        return $node;
      }

      // If node has translation, we return the translation.
      if ($node->hasTranslation($sku->get('langcode')->getString())) {
        return $node->getTranslation($sku->get('langcode')->getString());
      }

      // If translation not available and create_translation flag is true.
      if ($create_translation && $sku->get('langcode')->getString() != LanguageInterface::LANGCODE_NOT_SPECIFIED) {
        return $node->addTranslation($sku->get('langcode')->getString());
      }

      return NULL;
    }

    return $node;
  }

}
