<?php

namespace Drupal\alshaya_stylefinder\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides Style Finder block.
 *
 * @Block(
 *   id = "alshaya_stylefinder",
 *   admin_label = @Translation("Alshaya Style Finder")
 * )
 */
class AlshayaStyleFinderBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Node Storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->nodeStorage = $entity_type_manager->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();
    $form['reference_quiz_node_id'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#title' => $this->t('Add the Quiz Node'),
      '#description' => $this->t('Select the respestive quiz.'),
      '#default_value' => isset($config['reference_quiz_node_id']) ? $this->nodeStorage->load($config['reference_quiz_node_id']) : '',
      '#tags' => TRUE,
      '#selection_settings' => [
        'target_bundles' => ['quiz'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['reference_quiz_node_id'] = $values['reference_quiz_node_id'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => '<div id="style-finder-container">abcd</div>',
      '#attached' => [
        'library' => [
          'alshaya_stylefinder/alshaya_stylefinder',
          'alshaya_white_label/alshaya-stylefinder',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), [
      'languages',
    ]);
  }

}
