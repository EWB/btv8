<?php

namespace Drupal\asset_injector\Entity;

use Drupal\asset_injector\AssetInjectorInterface;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Component\Utility\Unicode;

/**
 * Class AssetInjectorBase: Base asset injector class.
 *
 * @package Drupal\asset_injector\AssetInjectorBase.
 */
class AssetInjectorBase extends ConfigEntityBase implements AssetInjectorInterface {

  /**
   * Extension of the asset. Override in entity.
   *
   * @var string
   */
  public $extension = NULL;

  /**
   * The Js Injector ID.
   *
   * @var string
   */
  public $id;

  /**
   * The Js Injector label.
   *
   * @var string
   */
  public $label;

  /**
   * The code of the asset.
   *
   * @var string
   */
  public $code;

  /**
   * Themes to apply.
   *
   * @var array
   */
  public $themes;

  /**
   * Whitelist/blacklist pages.
   *
   * @var boolean
   */
  public $visibility;

  /**
   * Pages to whitelist/blacklist.
   *
   * @var string
   */
  public $pages;

  /**
   * Node type to apply asset.
   *
   * @var string
   */
  public $nodeType;

  /**
   * Checks if the theme & page settings are appropriate for the given page.
   *
   * @return bool
   *   If the asset is enabled & applicable to current page.
   */
  public function isActive() {
    if (!$this->status()) {
      return FALSE;
    }

    $theme = \Drupal::theme()->getActiveTheme()->getName();

    if (empty($this->themes) || in_array($theme, $this->themes)) {
      if (!empty($this->nodeType)) {
        $node = \Drupal::routeMatch()->getParameter('node');
        if ($node && $node->getType() == $this->nodeType) {
          return TRUE;
        }
        else {
          return FALSE;
        }
      }

      $pages = rtrim($this->pages);
      if (empty($pages)) {
        return TRUE;
      }

      $path = \Drupal::service('path.current')->getPath();
      $path_alias = Unicode::strtolower(\Drupal::service('path.alias_manager')
        ->getAliasByPath($path));
      $page_match = \Drupal::service('path.matcher')
          ->matchPath($path_alias, $pages) || (($path != $path_alias) && \Drupal::service('path.matcher')
            ->matchPath($path, $pages));

      // When $rule->visibility has a value of 0, the js is
      // added on all pages except those listed in $rule->pages.
      // When set to 1, it is added only on those pages listed in $rule->pages.
      if (!($this->visibility xor $page_match)) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
