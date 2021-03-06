<?php
/**
 * @file
 * Preprocessors and theme functions of the Superfish module.
 */

use Drupal\Core\Url;
use Drupal\Core\Menu\MenuLinkDefault;
use Drupal\Core\Menu\InaccessibleMenuLink;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Template\Attribute;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\Component\Utility\SafeMarkup;

/**
 * Prepares variables for the Superfish menu template.
 *
 * Default template: superfish.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - element: An associative array containing the properties of the element.
 *     Properties used: #menu_name, #html_id, #settings, #tree
 *     - menu_name: Unique menu identifier.
 *     - html_id: Unique HTML ID.
 *     - settings: Menu block settings.
 *     - tree: The menu tree.
 *
 * @see superfish.html.twig
 */

function template_preprocess_superfish(array &$variables) {
  $element = $variables['element'];

  $menu_items_rendered = array(
    '#theme' => 'superfish_menu_items',
    '#menu_name' => $element['#menu_name'],
    '#tree' => $element['#tree'],
    '#settings' => $element['#settings'],
    '#cloned_parent' => false
  );

  $menu_classes = array('menu', 'sf-menu');
  $menu_classes[] = 'sf-' . $element['#menu_name'];
  $menu_classes[] = 'sf-' . $element['#settings']['menu_type'];
  $menu_classes[] = 'sf-style-' . $element['#settings']['style'];
  $menu_classes[] = (\Drupal::languageManager()->getCurrentLanguage()->getDirection() === LanguageInterface::DIRECTION_RTL) ? 'rtl' : '';
  if (strpos($element['#settings']['ulclass'], ' ') !== FALSE) {
    $l = explode(' ', $element['#settings']['ulclass']);
    foreach ($l as $c) {
      $menu_classes[] = Html::cleanCssIdentifier($c);
    }
  }
  else {
    $menu_classes[] = Html::cleanCssIdentifier($element['#settings']['ulclass']);
  }
  $menu_classes = implode(' ', superfish_array_remove_empty($menu_classes));

  $variables['id'] = $element['#html_id'];
  $variables['menu_classes'] = $menu_classes;
  $variables['menu_items'] = $menu_items_rendered;

}

/**
 * Prepares variables for Superfish menu items templates.
 *
 * Default template: superfish-menu-items.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - element: An associative array containing the properties of the element.
 *     Properties used: #tree, #settings, #cloned_parent
 *     - tree: The menu tree.
 *     - menu_name: Unique menu identifier.
 *     - settings: Block settings
 *     - cloned_parent: Cloned sub-menu parent link.
 *
 * @see superfish-menu-items.html.twig
 */

function template_preprocess_superfish_menu_items(array &$variables) {

  $element = $variables['element'];

  // Keep $sfsettings untouched as we need to pass it to the child menus.
  $settings = $sfsettings = $element['#settings'];
  $multicolumn = $multicolumn_below = $settings['multicolumn'];

  $variables['menu_items'] = array();

  $menu = $element['#tree'];

  // sfTouchscreen.
  // Adding cloned parent to the sub-menu tree.
  // Note, it is always false if it's not a sub-menu.
  if ($element['#cloned_parent'] !== FALSE) {
    array_unshift($menu, $element['#cloned_parent']);
  }

  $active_trails = \Drupal::service('menu.active_trail')->getActiveTrailIds($element['#menu_name']);

  foreach ($menu as $key => $menu_item) {

    if (null !== $menu_item->link && !($menu_item->link instanceof InaccessibleMenuLink)) {

      $item_class = $link_class = array();
      $multicolumn_wrapper = $multicolumn_column = $multicolumn_content = FALSE;

      // Menu link properties.
      $link = $menu_item->link->getPluginDefinition();

      $item = array(
        'id'            => $link['id'],
        'text'          => $menu_item->link->getTitle(),
        'description'   => $menu_item->link->getDescription(),
        'url'           => $menu_item->link->getUrlObject(),
        'enabled'       => $link['enabled'],
        'expanded'      => $sfsettings['expanded'] ? $link['expanded'] : TRUE,
        'options'       => $link['options'],
        'subtree'       => $menu_item->subtree,
        'depth'         => $menu_item->depth,
        'hasChildren'   => $menu_item->hasChildren,
        'inActiveTrail' => $menu_item->inActiveTrail
      );

      // Adding the necessary "active-trail" class.
      if (array_key_exists($key, $active_trails) || $item['inActiveTrail'] != FALSE) {
        $item_class[] = 'active-trail';
      }

      // Add menu link depth classes to the <li> element and its link.
      $link_class[] = $settings['itemdepth'] ? 'sf-depth-' . $item['depth'] : '';
      $item_class[] = $settings['itemdepth'] ? 'sf-depth-' . $item['depth'] : '';
      // Indicates a cloned parent link, i.e. does not exist in the actual menu tree.
      $item_class[] = $element['#cloned_parent'] ? 'sf-clone-parent' : '';

      // Adding custom <li> classes.
      if (strpos($settings['liclass'], ' ') !== FALSE) {
        $l = explode(' ', $settings['liclass']);
        foreach ($l as $c) {
          $item_class[] = Html::cleanCssIdentifier($c);
        }
      }
      else {
        $item_class[] = Html::cleanCssIdentifier($settings['liclass']);
      }

      // Adding custom link classes.
      if (strpos($settings['hlclass'], ' ') !== FALSE) {
        $l = explode(' ', $settings['hlclass']);
        foreach ($l as $c) {
          $link_class[] = Html::cleanCssIdentifier($c);
        }
      }
      else {
        $link_class[] = Html::cleanCssIdentifier($settings['hlclass']);
      }

      // Add a class to external links.
      $link_class[] = isset($item['options']['external']) ? 'sf-external' : '';

      // Inserting link description (the "title" attribute) into the text.
      if ($settings['add_linkdescription'] && !empty($item['description'])) {
        $link_text = '@text <span class="sf-description">@description</span>';
        $link_text_replace = array('@text' => $item['text'], '@description' => $item['description']);
      } else {
        $link_text = '@text';
        $link_text_replace = array('@text' => $item['text']);
      }

      // Hiding link descriptions (the "title" attribute).
      if ($settings['hide_linkdescription']) {
        // TODO
      }

      // Sub-menu.
      if ($item['hasChildren'] && $item['subtree'] && $item['expanded']) {

        // Multi-column sub-menus.
        if ($settings['multicolumn']) {
          $multicolumn_wrapper = ($item['depth'] == $settings['multicolumn_depth']) ? TRUE : FALSE;
          $multicolumn_column = ($item['depth'] == $settings['multicolumn_depth'] + 1) ? TRUE : FALSE;
          $multicolumn_content = ($item['depth'] >= $settings['multicolumn_depth'] && $item['depth'] <= $settings['multicolumn_levels']) ? TRUE : FALSE;
        }

        // sfTouchscreen.
        // Preparing the cloned parent links to be added to the sub-menus.
        if ($settings['clone_parent'] && $item['subtree']) {
          $cloned_parent = $menu_item;
          $cloned_parent->subtree = [];
        }
        else {
          $cloned_parent = FALSE;
        }

        // Render the sub-menu.
        $children = array(
          '#theme'         => 'superfish_menu_items',
          '#menu_name'     => $element['#menu_name'],
          '#tree'          => $item['subtree'],
          '#settings'      => $sfsettings,
          '#cloned_parent' => $cloned_parent
        );

        if ($item['subtree']) {
          // Adding some more classes.
          $item_class[] = $multicolumn_column ? 'sf-multicolumn-column' : '';
          $item_class[] = $link_class[] = 'menuparent';
        }
      }
      else {
        $children = '';
        $item_class[] = 'sf-no-children';
      }

      // Preparing <li> classes for the theme.
      $item_class = implode(' ', superfish_array_remove_empty($item_class));

      // Merging link classes.
      if (isset($item['options']['attributes']['class'])) {
        $link_class_current = $item['options']['attributes']['class'];
        $link_class = array_merge($link_class_current, superfish_array_remove_empty($link_class));
      }
      $item['options']['attributes']['class'] = superfish_array_remove_empty($link_class);

      // Dirty fix! to only add a "menuparent" class.
      $item['options_menuparent'] = $item['options'];
      $item['options_menuparent']['attributes']['class'][] = 'menuparent';
      $link_element = array(
        '#type' => 'link',
        '#title' => SafeMarkup::format($link_text, $link_text_replace),
        '#url' => $item['url'],
        '#options' => $item['options']
      );
      $link_element_menuparent = array(
        '#type' => 'link',
        '#title' => SafeMarkup::format($link_text, $link_text_replace),
        '#url' => $item['url'],
        '#options' => $item['options_menuparent']
      );

      $variables['menu_items'][] = array(
        'id'                  => Html::getUniqueId($element['#menu_name'] .'-'. $item['id']),
        'item_class'          => $item_class,
        'link'                => $link_element,
        'link_menuparent'     => $link_element_menuparent,
        'children'            => $children,
        'multicolumn_wrapper' => $multicolumn_wrapper,
        'multicolumn_content' => $multicolumn_content,
        'multicolumn_column'  => $multicolumn_column
      );
    }
  }
}
