<?php

namespace Drupal\alshaya_stylefinder\Plugin\Block;

use Drupal\alshaya_i18n\AlshayaI18nLanguages;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;

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
   * Entity Storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityTypeManager;
  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;
  /**
   * Entity Repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

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
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   Entity Repository service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              EntityTypeManagerInterface $entity_type_manager,
                              LanguageManagerInterface $language_manager,
                              EntityRepositoryInterface $entityRepository
                            ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->entityRepository = $entityRepository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('language_manager'),
      $container->get('entity.repository')
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
      '#title' => $this->t('Create a quiz'),
      '#description' => $this->t('Allows to create a quiz.'),
      '#default_value' => isset($config['reference_quiz_node_id']) ? $this->entityTypeManager->getStorage('node')->load($config['reference_quiz_node_id'][0]['target_id']) : '',
      '#tags' => TRUE,
      '#selection_settings' => [
        'target_bundles' => ['quiz'],
      ],
    ];
    $form['dy_strategy_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('DY Strategy Id'),
      '#required' => TRUE,
      '#description' => $this->t('Dynamic Yield Strategy Id. Required for product recommendations.'),
      '#default_value' => $config['dy_strategy_id'] ?? '',
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
    $this->configuration['dy_strategy_id'] = $values['dy_strategy_id'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $current_langcode = $this->languageManager->getCurrentLanguage()->getId();
    $quiz_node_id = $config['reference_quiz_node_id'][0]['target_id'];
    $quizDetails = [];
    $cache_tags = [];
    if (!empty($quiz_node_id)) {
      $quiz_node = $this->entityTypeManager->getStorage('node')->load($quiz_node_id);
      if ($quiz_node instanceof NodeInterface
        && $quiz_node->hasTranslation($current_langcode)) {
        // Get the Translated node of the current language code.
        $quiz_node = $this->entityRepository->getTranslationFromContext($quiz_node, $current_langcode);
        $cache_tags = Cache::mergeTags($cache_tags, array_merge($quiz_node->getCacheTags()));
        $quizDetails['quiz_title'] = $quiz_node->title->value;
        $quizDetails['quiz_instruction'] = strip_tags($quiz_node->field_instruction->value) ?? NULL;
        $quizDetails['quiz_type'] = $quiz_node->field_quiz_type->value ?? NULL;
        foreach ($quiz_node->field_quiz_question as $question) {
          $ques_nid = $question->target_id;
          $question_details[] = $this->quizQuestionDetails($ques_nid);
        }
        $quizDetails['question'] = $question_details;
      }
    }
    return [
      '#markup' => '<div id="style-finder-container"></div>',
      '#attached' => [
        'library' => [
          'alshaya_stylefinder/alshaya_stylefinder',
          'alshaya_white_label/alshaya-stylefinder',
        ],
        'drupalSettings' => [
          'styleFinder' => [
            'quizDetails' => $quizDetails,
            'dyStrategyId' => $config['dy_strategy_id'],
            'locale' => AlshayaI18nLanguages::getLocale($current_langcode),
          ],
        ],
      ],
      '#cache' => [
        'tags' => $cache_tags,
      ],
    ];
  }

  /**
   * Custom Function to return question node details.
   *
   * @param mixed $q_nid
   *   Node id of the question.
   *
   * @return array
   *   The field details from the node.
   */
  private function quizQuestionDetails($q_nid) {
    $question_details = [];
    $current_langcode = $this->languageManager->getCurrentLanguage()->getId();
    $question_node = $this->entityTypeManager->getStorage('node')->load($q_nid);
    if ($question_node instanceof NodeInterface
      && $question_node->hasTranslation($current_langcode)) {
      // Get the Translated node of the current language code.
      $question_node = $this->entityRepository->getTranslationFromContext($question_node, $current_langcode);
      $question_details['ques_instruction'] = strip_tags($question_node->field_instruction->value) ?? NULL;
      $question_details['title'] = $question_node->title->value;
      foreach ($question_node->field_answer as $answer) {
        $answer_nid = $answer->target_id;
        $answer_details[$answer_nid] = $this->quizAnswerDetails($answer_nid);
      }
      $question_details['answer'] = $answer_details;
    }
    return $question_details;
  }

  /**
   * Custom Function to return answer node details.
   *
   * @param mixed $a_nid
   *   Node id of the question.
   *
   * @return array
   *   The field details from the node.
   */
  private function quizAnswerDetails($a_nid) {
    $taxonomy_term_trans = NULL;
    $answer_details = [];
    $current_langcode = $this->languageManager->getCurrentLanguage()->getId();
    $answer_node = $this->entityTypeManager->getStorage('node')->load($a_nid);
    if ($answer_node instanceof NodeInterface
      && $answer_node->hasTranslation($current_langcode)) {
      // Get the Translated node of the current language code.
      $answer_node = $this->entityRepository->getTranslationFromContext($answer_node, $current_langcode);
      // To get the Product Image URL.
      $image_id = $answer_node->field_product_image->target_id;
      $imageSrc = NULL;
      if (!empty($image_id)) {
        $imageSrc = $this->getFileUrlFromId($image_id);
      }

      // To get the next question details if present.
      if ($answer_node->hasField('field_next_question')) {
        $next_question = $answer_node->get('field_next_question')->getValue();
      }
      $next_ques_details = [];
      if (!empty($next_question)) {
        foreach ($next_question as $next_ques) {
          $next_ques_nid = $next_ques['target_id'];
          $next_ques_details[] = $this->quizQuestionDetails($next_ques_nid);
        }
      }

      // To fetch the choice of Answer node.
      $choice = NULL;
      if (!empty($answer_node->field_choice_4->target_id)) {
        $term_id = $answer_node->field_choice_4->target_id;
        $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($term_id);

        if ($term instanceof TermInterface
          && $term->hasTranslation($current_langcode)) {
          $taxonomy_term_trans = $this->entityRepository->getTranslationFromContext($term, $current_langcode);
          $choice = $taxonomy_term_trans->getName();
          $answer_details['attrCode'] = $taxonomy_term_trans->get('field_sku_attribute_code')->value;
        }

        $answer_details['choice'] = $choice;
      }
      else {
        if ($answer_node->field_choice_1->value) {
          $answer_details['choice'] = $answer_node->field_choice_1->value ?? NULL;
          $answer_details['attrCode'] = 'product_category';
        }
        if ($answer_node->field_choice_2->value) {
          $answer_details['choice'] = $answer_node->field_choice_2->value ?? NULL;
          $answer_details['attrCode'] = 'padding';
        }
        if ($answer_node->field_choice_3->value) {
          $answer_details['choice'] = $answer_node->field_choice_3->value ?? NULL;
          $answer_details['attrCode'] = 'bra_coverage';
        }
      }

      $uri = $answer_node->get('field_quiz_see_more_url')->getValue();
      $uri = !empty($uri) ? $uri[0]['uri'] : '';
      $answer_details['see_more_reference'] = $uri ? str_replace('internal:/', '', $uri) : '';

      $answer_details['title'] = $answer_node->title->value;
      $answer_details['description'] = strip_tags($answer_node->field_answer_summary->value) ?? NULL;
      $answer_details['image_url'] = $imageSrc;
      $answer_details['question'] = $next_ques_details;
      $answer_details['nid'] = $answer_node->id();
    }
    return $answer_details;
  }

  /**
   * Returns Image path.
   *
   * @param int $file_target_id
   *   The id of the image file.
   *
   * @return string
   *   The URL of the Image file.
   */
  public function getFileUrlFromId(int $file_target_id) {
    $file_url = "";
    if ($file_target_id) {
      $file = $this->entityTypeManager->getStorage('file')->load($file_target_id);
      $file_url = file_create_url($file->getFileUri());
    }
    return $file_url;
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
