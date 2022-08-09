<?php

namespace Drupal\alshaya_search\EventSubscriber;

use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Solarium\Core\Event\Events;
use Solarium\Core\Event\PreExecute as PreExecuteEvent;

/**
 * Class Alshaya Search Autocomplete Param.
 */
class AlshayaSearchAutocompleteParam implements EventSubscriberInterface {

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * AlshayaSearchAutocompleteParam constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Language manager.
   */
  public function __construct(LanguageManagerInterface $languageManager) {
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[Events::PRE_EXECUTE][] = ['onPreExecute'];
    return $events;
  }

  /**
   * Event handler to add the language in field query for auto complete.
   *
   * @param \Solarium\Core\Event\PreExecute $event
   *   Event object.
   */
  public function onPreExecute(PreExecuteEvent $event) {
    $query = $event->getQuery();
    $options = $query->getOptions();
    // Only for the auto complete as auto complete uses the term component.
    if ($options['handler'] == 'terms' && !empty($options['fields']) && is_array($options['fields'])) {
      $field = $options['fields'][0];
      // In auto complete, field to query is 'spell' and we need to change it
      // to according to language and thus adding lang code. So it will be like
      // 'spell_ar' or 'spell_en'.
      if ($field == 'spell') {
        $options['fields'][0] = $field . '_' . $this->languageManager->getCurrentLanguage()->getId();
        $query->setOptions($options);
      }
    }
  }

}
