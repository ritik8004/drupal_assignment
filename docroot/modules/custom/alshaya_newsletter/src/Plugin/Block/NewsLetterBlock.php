<?php

namespace Drupal\alshaya_newsletter\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides newsletter subscription block.
 *
 * @Block(
 *   id = "alshaya_newsletter_subscription",
 *   admin_label = @Translation("Alshaya Newsletter")
 * )
 */
class NewsLetterBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * NewsLetterBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin defination.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   Form builder service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $post_url = isset($this->configuration['subscription_post_url']) ? $this->configuration['subscription_post_url'] : '';
    $form = $this->formBuilder->getForm('Drupal\alshaya_newsletter\Form\NewsLetterForm', ['post_url' => $post_url]);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['subscription_post_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subscription URL to POST to'),
      '#description' => $this->t('Please provide link where you want to post to'),
      '#default_value' => isset($this->configuration['subscription_post_url']) ? $this->configuration['subscription_post_url'] : '',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $post_to_link = $form_state->getValue('subscription_post_url');
    $this->configuration['subscription_post_url'] = $post_to_link;
  }

}
