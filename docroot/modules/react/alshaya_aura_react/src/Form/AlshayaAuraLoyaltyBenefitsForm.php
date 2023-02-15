<?php

namespace Drupal\alshaya_aura_react\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Alshaya AURA Loyalty Benefits.
 */
class AlshayaAuraLoyaltyBenefitsForm extends ConfigFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a EntityManager object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   A list of entity definition objects.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_aura_react_loyalty_benefits';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_aura_react.loyalty_benefits'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['alshaya_aura_react']['loyalty_benefits_title1'] = [
      '#type' => 'textfield',
      '#title' => $this->t('AURA Loyalty Benefits Title 1'),
      '#description' => $this->t('AURA Loyalty Benefits Title 1 for Loyalty Club page.'),
      '#default_value' => $this->config('alshaya_aura_react.loyalty_benefits')->get('loyalty_benefits_title1'),
    ];

    $form['alshaya_aura_react']['loyalty_benefits_title2'] = [
      '#type' => 'textfield',
      '#title' => $this->t('AURA Loyalty Benefits Title 2'),
      '#description' => $this->t('AURA Loyalty Benefits Title 2 for Loyalty Club page.'),
      '#default_value' => $this->config('alshaya_aura_react.loyalty_benefits')->get('loyalty_benefits_title2'),
    ];

    $form['alshaya_aura_react']['loyalty_benefits_content'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('AURA Loyalty Benefits Content - ONLY HTML for TABLE CONTENT - CHECK HELP'),
      '#description' => $this->t('Enter HTML as per guidelines defined in HELP section. This field doesnt support CSS or JS. This content is passed to React for rendering entering anything other than approved HTML will break the layout of the page.'),
      '#default_value' => $this->config('alshaya_aura_react.loyalty_benefits')->get('loyalty_benefits_content.value'),
    ];

    $node = NULL;
    if ($this->config('alshaya_aura_react.loyalty_benefits')->get('loyalty_static_content_node')) {
      $node_storage = $this->entityManager->getStorage('node');
      $node = $node_storage->load($this->config('alshaya_aura_react.loyalty_benefits')->get('loyalty_static_content_node'));
    }

    $form['alshaya_aura_react']['loyalty_static_content_node'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Aura landing page'),
      '#target_type' => 'node',
      '#selection_setttings' => [
        'target_bundles' => ['static_html'],
      ],
      '#default_value' => $node,
      '#description' => $this->t('Please select the Aura landing page which will be used to get static HTML content.'),
    ];

    // Display token UI required for currency.
    $form['token_help'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => ['alshaya_aura'],
    ];

    $form['alshaya_aura_react']['help'] = [
      '#type' => 'details',
      '#title' => $this->t('Help - Content Visual Reference'),
      '#description' => $this->t('Visual reference for the different items that can be configured via this form.'),
      '#open' => FALSE,
      '#weight' => -5,
    ];

    $form['alshaya_aura_react']['help']['title'] = [
      '#type' => 'markup',
      '#markup' => '<img width=500 loading="lazy" src="/modules/react/alshaya_aura_react/assets/help-images/titles.png">',
    ];

    $form['alshaya_aura_react']['help']['table'] = [
      '#type' => 'markup',
      '#markup' => '<img width=500 loading="lazy" src="/modules/react/alshaya_aura_react/assets/help-images/table.png">',
    ];

    $form['alshaya_aura_react']['help_html'] = [
      '#type' => 'details',
      '#title' => $this->t('Help - Benefits ROW HTML Snippets Reference'),
      '#open' => FALSE,
      '#weight' => -5,
    ];

    $form['alshaya_aura_react']['help_html']['rule1'] = [
      '#type' => 'markup',
      '#markup' => '<pre>1. We can contribute content only for the table rows, we cant add new columns as that would affect the layout and styles.</pre>',
    ];

    $form['alshaya_aura_react']['help_html']['rule2'] = [
      '#type' => 'markup',
      '#markup' => '<pre>2. We can add/remove/update as many rows as we like, but each row will have 4 cells or columns.</pre>',
    ];

    $form['alshaya_aura_react']['help_html']['rule3'] = [
      '#type' => 'markup',
      '#markup' => '<pre>3. All HTML we contribute becomes part of Table content. Additional HTML/CSS/JS not supported. </pre>',
    ];

    $form['alshaya_aura_react']['help_html']['rule4'] = [
      '#type' => 'markup',
      '#markup' => '<pre></pre><pre><b>4. Header Row </b></pre><code>' . htmlspecialchars('<div class="aura-loyalty-benefits-row header"><div>Plan Tiers</div><div>Hello</div><div>Star</div><div>VIP</div></div>') . '</code>',
    ];

    $form['alshaya_aura_react']['help_html']['rule5'] = [
      '#type' => 'markup',
      '#markup' => '<pre></pre><pre><b>5. Bold Row </b></pre><code>' . htmlspecialchars('<div class="aura-loyalty-benefits-row item-bold"><div>Spend per Calendar Year</div><div>FREE</div><div>KWD 500</div><div>KWD 1000</div></div>') . '</code>',
    ];

    $form['alshaya_aura_react']['help_html']['rule5'] = [
      '#type' => 'markup',
      '#markup' => '<pre></pre><pre><b>6. Normal Row </b></pre><code>' . htmlspecialchars('<div class="aura-loyalty-benefits-row normal"><div>Points Per KD 1</div><div>10 Points</div><div>15 Points</div><div>20 Points</div></div>') . '</code>',
    ];

    $form['alshaya_aura_react']['help_html']['rule6'] = [
      '#type' => 'markup',
      '#markup' => '<pre></pre><pre><b>7. Normal Row with Stars </b></pre><code>' . htmlspecialchars('<div class="aura-loyalty-benefits-row stars-row"><div>Seasonal Saving</div><div><div class="star-icon"></div></div><div><div class="star-icon"></div><div class="star-icon"></div></div><div><div class="star-icon"></div><div class="star-icon"></div><div class="star-icon"></div></div></div>') . '</code>',
    ];

    $form['alshaya_aura_react']['help_html']['rule7'] = [
      '#type' => 'markup',
      '#markup' => '<pre></pre><pre><b>8. Normal Row with Ticks</b></pre><code>' . htmlspecialchars('<div class="aura-loyalty-benefits-row tick-row"><div>Free Standard Shipping</div><div><div class="hyphen">-</div></div><div><div class="tick-icon"></div></div><div><div class="tick-icon"></div></div></div>') . '</code>',
    ];

    $form['alshaya_aura_react']['help_html']['rule8'] = [
      '#type' => 'markup',
      '#markup' => '<pre></pre><pre><b>9. Star Icon inside a row cell</b></pre><code>' . htmlspecialchars('<div class="star-icon"></div>') . '</code>',
    ];

    $form['alshaya_aura_react']['help_html']['rule9'] = [
      '#type' => 'markup',
      '#markup' => '<pre></pre><pre><b>10. Tick Icon inside a row cell</b></pre><code>' . htmlspecialchars('<div class="tick-icon"></div>') . '</code>',
    ];

    $form['alshaya_aura_react']['help_html']['rule10'] = [
      '#type' => 'markup',
      '#markup' => '<pre></pre><pre><b>11. Empty Cell with Hyphen</b></pre><code>' . htmlspecialchars('<div class="hyphen">-</div>') . '</code>',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_aura_react.loyalty_benefits')
      ->set('loyalty_benefits_title1', $form_state->getValue('loyalty_benefits_title1'))
      ->set('loyalty_benefits_title2', $form_state->getValue('loyalty_benefits_title2'))
      ->set('loyalty_benefits_content', $form_state->getValue('loyalty_benefits_content'))
      ->set('loyalty_static_content_node', $form_state->getValue('loyalty_static_content_node'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
