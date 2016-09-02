<?php

namespace Drupal\asset_injector\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;

/**
 * Class CssInjectorForm.
 *
 * @package Drupal\asset_injector\Form
 */
class AssetInjectorFormBase extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $entity = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $entity->label(),
      '#description' => $this->t('Label for the @type.', [
        '@type' => $entity->getEntityType()->getLabel(),
      ]),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $entity->id(),
      '#machine_name' => [
        'exists' => '\\' . $entity->getEntityType()->getClass() . '::load',
        'replace_pattern' => '[^a-z0-9_]+',
        'replace' => '_',
      ],
      '#disabled' => !$entity->isNew(),
    ];

    $form['code'] = [
      '#type' => 'textarea',
      '#title' => t('Code'),
      '#description' => t('The actual code goes in here.'),
      '#rows' => 10,
      '#default_value' => $entity->code,
      '#required' => TRUE,
      '#prefix' => '<div>',
      '#suffix' => '<div class="resizable"><div class="ace-editor"></div></div></div>',
    ];

    // Advanced options fieldset.
    $form['advanced'] = [
      '#type' => 'fieldset',
      '#title' => t('Advanced options'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];

    $node_types = NodeType::loadMultiple();
    foreach ($node_types as $key => &$type) {
      $type = $type->get('name');
    }
    $node_types[''] = $this->t('-- None --');
    asort($node_types);

    $form['advanced']['nodeType'] = [
      '#type' => 'select',
      '#title' => $this->t('Node Type'),
      '#options' => $node_types,
      '#default_value' => $entity->nodeType,
    ];

    $themes = [];
    foreach (system_list('theme') as $key => $theme) {
      if ($theme->status) {
        $themes[$key] = $theme->info['name'];
      }
    }

    $form['advanced']['themes'] = [
      '#type' => 'select',
      '#title' => $this->t('Theme'),
      '#description' => t('Which themes is the @type used. Leave unselected to apply to all themes.', [
        '@type' => $entity->getEntityType()->getLabel(),
      ]),
      '#options' => $themes,
      '#multiple' => TRUE,
      '#default_value' => $entity->themes,
    ];
    $form['page_visibility'] = [
      '#type' => 'fieldset',
      '#title' => t('Pages'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#states' => array(
        'visible' => array(
          ':input[name="nodeType"]' => array('value' => ''),
        ),
      ),
    ];
    $form['page_visibility']['visibility'] = [
      '#type' => 'radios',
      '#title' => t('Add @type to specific pages', [
        '@type' => $entity->getEntityType()->getLabel(),
      ]),
      '#options' => [
        0 => t('Every page except the listed pages'),
        1 => t('The listed pages only'),
      ],
      '#default_value' => $entity->visibility ? $entity->visibility : 0,
    ];
    $form['page_visibility']['pages'] = [
      '#type' => 'textarea',
      '#title' => t('Pages'),
      '#title_display' => 'invisible',
      '#default_value' => $entity->pages,
      '#description' => t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. Example paths are %blog for the blog page and %blog-wildcard for every personal blog. %front is the front page.", [
        '%blog' => '/blog',
        '%blog-wildcard' => '/blog/*',
        '%front' => '<front>',
      ]),
      '#rows' => 5,
    ];
    $form['#attached']['library'][] = 'asset_injector/ace-editor';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function actionsElement(array $form, FormStateInterface $form_state) {
    $element = parent::actionsElement($form, $form_state);
    $element['saveContinue'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and Continue Editing'),
      '#name' => 'save_continue',
      '#submit' => ['::submitForm', '::save'],
      '#weight' => 7,
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $directory = file_build_uri('asset_injector');
    file_prepare_directory($directory, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);

    $entity = $this->entity;
    $status = $entity->save();

    switch ($status) {
      case SAVED_NEW:
        $message = $this->t('Created the %label Asset Injector.', [
          '%label' => $entity->label(),
        ]);
        $log = '%type asset %id created';
        break;

      default:
        $message = $this->t('Saved the %label Asset Injector.', [
          '%label' => $entity->label(),
        ]);
        $log = '%type asset %id saved';
    }
    drupal_set_message($message);
    \Drupal::logger('asset_injector')->notice($log, [
      '%type' => $entity->get('entityTypeId'),
      '%id' => $entity->id,
    ]);

    drupal_flush_all_caches();
    $trigger = $form_state->getTriggeringElement();
    if (isset($trigger['#name']) && $trigger['#name'] != 'save_continue') {
      $form_state->setRedirectUrl($entity->toUrl('collection'));
    }
    else {
      $form_state->setRedirectUrl($entity->toUrl());
    }

  }

}
