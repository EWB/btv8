<?php

namespace Drupal\asset_injector\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class JsInjectorForm.
 *
 * @package Drupal\asset_injector\Form
 */
class JsInjectorForm extends AssetInjectorFormBase {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $entity = $this->entity;
    $form['advanced']['jquery'] = [
      '#type' => 'checkbox',
      '#title' => t('Include jQuery'),
      '#description' => t('Not all pages load jQuery by default. Select this to include jQuery when loading this asset.'),
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#default_value' => $entity->jquery,
    ];

    $form['advanced']['preprocess'] = [
      '#type' => 'checkbox',
      '#title' => t('Preprocess JS'),
      '#description' => t('If the JS is preprocessed, and JS aggregation is enabled, the script file will be aggregated.'),
      '#default_value' => $entity->preprocess,
    ];
    $form['code']['#attributes']['data-ace-mode'] = 'javascript';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $js_injector = $this->entity;
    $file_name = file_build_uri('://asset_injector/' . $js_injector->id . '.js');
    if (file_exists($file_name)) {
      file_unmanaged_delete_recursive($file_name);
    }
    parent::save($form, $form_state);
  }

}
