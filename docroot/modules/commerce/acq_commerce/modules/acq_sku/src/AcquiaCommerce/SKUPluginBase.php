<?php

namespace Drupal\acq_sku\AcquiaCommerce;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\acq_sku\Entity\SKU;
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
    $display_node = $this->getDisplayNode($sku);
    $url = $display_node->toUrl();
    $link = Link::fromTextAndUrl($sku->label(), $url);
    $renderArray = $link->toRenderable();
    return render($renderArray);
  }

  /**
   * {@inheritdoc}
   */
  public function getDisplayNode(SKU $sku) {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'acq_product')
      ->condition('field_skus', $sku->getSku())
      ->range(0, 1);

    $result = $query->execute();
    $nid = reset($result);

    return Node::load($nid);
  }

}
