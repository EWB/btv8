<?php

namespace Drupal\asset_injector\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class CssInjectorForm.
 *
 * @package Drupal\asset_injector\Form
 */
class CssInjectorForm extends AssetInjectorFormBase {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $entity = $this->entity;
    $form['advanced']['media'] = [
      '#type' => 'select',
      '#title' => 'Media',
      '#description' => t('Which media types is the CSS used.'),
      '#options' => [
        'all' => t('All'),
        'print' => t('Print'),
        'screen' => t('Screen'),
      ],
      '#default_value' => $entity->media,
    ];
    $form['advanced']['preprocess'] = [
      '#type' => 'checkbox',
      '#title' => t('Preprocess CSS'),
      '#description' => t('If the CSS is preprocessed, and CSS aggregation is enabled, the script file will be aggregated.'),
      '#default_value' => $entity->preprocess,
    ];
    $form['code']['#attributes']['data-ace-mode'] = 'css';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $file_name = file_build_uri('asset_injector/' . $entity->id . '.css');
    if (file_exists($file_name)) {
      file_unmanaged_delete_recursive($file_name);
    }
    parent::save($form, $form_state);
  }

}
