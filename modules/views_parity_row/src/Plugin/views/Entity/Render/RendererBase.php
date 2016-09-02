<?php

/**
 * @file
 * Contains \Drupal\views_parity_row\Plugin\views\Entity\Render\RendererBase.
 */

namespace Drupal\views_parity_row\Plugin\views\Entity\Render;

use Drupal\views\Entity\Render\RendererBase as ViewsRendererBase;

/**
 * Renders entities in the current language.
 */
abstract class RendererBase extends ViewsRendererBase {

  /**
   * {@inheritdoc}
   */
  public function preRender(array $result) {
    $view_builder = $this->view->rowPlugin->entityManager->getViewBuilder($this->entityType->id());

    /** @var \Drupal\views\ResultRow $row */
    foreach ($result as $row) {
      $entity = $row->_entity;
      $entity->view = $this->view;

      $view_mode = $this->view->rowPlugin->options['view_mode'];

      if ($this->view->rowPlugin->options['views_parity_row_enable'] == TRUE) {
        $view_mode_override = FALSE;
        if ($row->index >= $this->view->rowPlugin->options['views_parity_row']['start']) {
          if ($this->view->rowPlugin->options['views_parity_row']['end'] != 0) {
            if ($row->index <= $this->view->rowPlugin->options['views_parity_row']['end']) {
              $view_mode_override = TRUE;
            }
          }
          else {
            $view_mode_override = TRUE;
          }
        }

        if ($view_mode_override == TRUE) {
          if (($row->index-$this->view->rowPlugin->options['views_parity_row']['start']) % $this->view->rowPlugin->options['views_parity_row']['frequency'] == 0) {
            $view_mode = $this->view->rowPlugin->options['views_parity_row']['view_mode'];
          }
        }
      }

      $this->build[$entity->id()] = $view_builder->view($entity, $view_mode, $this->getLangcode($row));
    }
  }

}
