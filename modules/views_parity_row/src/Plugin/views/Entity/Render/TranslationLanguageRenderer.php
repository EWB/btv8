<?php

/**
 * @file
 * Contains \Drupal\views_parity_row\Plugin\views\Entity\Render\TranslationLanguageRenderer.
 */

namespace Drupal\views_parity_row\Plugin\views\Entity\Render;

use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\views\ResultRow;

/**
 * Renders entities in the current language.
 */
class TranslationLanguageRenderer extends RendererBase {

  /**
   * {@inheritdoc}
   */
  public function getLangcode(ResultRow $row) {
    return isset($row->{$this->langcodeAlias}) ? $row->{$this->langcodeAlias} : $this->languageManager->getDefaultLanguage()->getId();
  }

  /**
   * {@inheritdoc}
   */
  public function query(QueryPluginBase $query, $relationship = NULL) {
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $row) {
    $entity_id = $row->_entity->id();
    return $this->build[$entity_id];
  }
}
