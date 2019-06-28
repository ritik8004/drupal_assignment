<?php

namespace Drupal\alshaya_options_list\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides options list page menu block.
 *
 * @Block(
 *   id = "alshaya_options_list_menu",
 *   admin_label = @Translation("Alshaya options list page menu")
 * )
 */
class AlshayaOptionsListMenuBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'link_title' => $this->t('Shop by'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['link_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link title'),
      '#description' => $this->t('Title to be displayed for the link.'),
      '#default_value' => $this->configuration['link_title'],
    ];
    $form['link_align'] = [
      '#type' => 'radios',
      '#title' => $this->t('Link alignment'),
      '#description' => $this->t('Align the menu to the left or right.'),
      '#default_value' => $this->configuration['link_align'] ?? '',
      '#options' => [
        'left' => $this->t('Left'),
        'right' => $this->t('Right'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['link_title'] = $form_state->getValue('link_title');
    $this->configuration['link_align'] = $form_state->getValue('link_align');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $menu_title = $this->configuration['link_title'];
    $alignment_class = 'alshaya-options-' . $this->configuration['link_align'];
    $links = [];
    $pages = $this->configFactory->get('alshaya_options_list.admin_settings')->get('alshaya_options_pages');
    if (!empty($pages)) {
      foreach ($pages as $page) {
        $route_name = 'alshaya_options_list.options_page' . str_replace('/', '-', $page['url']);
        $links[] = Link::createFromRoute($page['menu-title'], $route_name, [])->toString();
      }
    }

    return [
      '#theme' => 'alshaya_options_menu_link',
      '#menu_title' => $menu_title,
      '#links' => $links,
      '#attached' => [
        'library' => [
          'alshaya_white_label/optionlist_menu',
        ],
      ],
      '#attributes' => [
        'class' => [$alignment_class, 'options-list-block'],
      ],
    ];
  }

}
