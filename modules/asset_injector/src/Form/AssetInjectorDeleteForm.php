<?php

namespace Drupal\asset_injector\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Builds the form to delete Css Injector entities.
 */
class AssetInjectorDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete @type: %name?', [
      '@type' => $this->entity->getEntityType()->getLabel(),
      '%name' => $this->entity->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    $type = $this->entity->getEntityType()->get('id');
    return new Url("entity.$type.collection");
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::logger('asset_injector')->notice('%type asset %id deleted', [
      '%type' => $this->entity->get('entityTypeId'),
      '%id' => $this->entity->id,
    ]);

    if ($ext = $this->entity->extension) {
      $id = $this->entity->id;
      $file_name = file_build_uri("asset_injector/$id.$ext");
      file_unmanaged_delete_recursive($file_name);
    }
    $this->entity->delete();

    drupal_set_message($this->t('@type deleted @label.', [
      '@type' => $this->entity->getEntityType()->getLabel(),
      '@label' => $this->entity->label(),
    ]));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
