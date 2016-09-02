<?php

namespace Drupal\asset_injector;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Asset Injector entities.
 */
class AssetInjectorListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Injector');
    $header['id'] = $this->t('Machine name');
    $header['themes'] = $this->t('Themes');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $data['label'] = $entity->label();
    $data['id'] = $entity->id();
    $themes = $entity->themes;
    if (empty($themes)) {
      $data['themes'] = $this->t('All');
    }
    else {
      $data['themes'] = implode(', ', $themes);
    }

    $row = [
      'class' => $entity->status() ? 'enabled' : 'disabled',
      'data' => $data + parent::buildRow($entity),
    ];
    return $row;
  }

}
