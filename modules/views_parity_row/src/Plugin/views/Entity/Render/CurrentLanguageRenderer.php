<?php

/**
 * @file
 * Contains \Drupal\views_parity_row\Plugin\views\Entity\Render\CurrentLanguageRenderer.
 */

namespace Drupal\views_parity_row\Plugin\views\Entity\Render;

use Drupal\views\ResultRow;

/**
 * Renders entities in the current language.
 */
abstract class CurrentLanguageRenderer extends RendererBase {

  /**
   * Returns NULL so that the current language is used.
   *
   * @param \Drupal\views\ResultRow $row
   *   The result row.
   */
  public function getLangcode(ResultRow $row) {
  }

}
