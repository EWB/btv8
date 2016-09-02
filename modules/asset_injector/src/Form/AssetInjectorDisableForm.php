<?php

namespace Drupal\asset_injector\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class AssetInjectorDisableForm.
 *
 * @package Drupal\asset_injector\Form
 */
class AssetInjectorDisableForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Disable @type: %label?', [
      '@type' => $this->entity->getEntityType()->getLabel(),
      '%label' => $this->entity->label(),
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->disable()->save();

    \Drupal::logger('asset_injector')->notice('%type asset %id disabled', [
      '%type' => $this->entity->get('entityTypeId'),
      '%id' => $this->entity->id,
    ]);

    parent::submitForm($form, $form_state);

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
