<?php

namespace Drupal\editorjs\Element;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\Hidden;

/**
 * Provides a form element for EditorJs.
 *
 * @FormElement("editorjs")
 */
class EditorJs extends Hidden {

  /**
   * {@inheritdoc}
   */
  public function getInfo(): array {
    $class = static::class;

    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processEditorJs'],
      ],
      '#pre_render' => [
        [$class, 'preRenderHidden'],
      ],
      '#theme' => 'input__hidden',
      '#theme_wrappers' => ['form_element'],
      '#attached' => [
        'library' => ['editorjs/init'],
      ],
    ];
  }

  /**
   * Expands a form element for add default properties.
   *
   * @param array $element
   *   The render element.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   * @param array $completeForm
   *   The complete form.
   *
   * @return array
   *   The render element.
   */
  public static function processEditorJs(array &$element, FormStateInterface $formState, array &$completeForm): array {
    self::attachLibraries($element);
    $element['#attributes']['class'][] = 'editorjs';

    $settings = [
      'tools' => $element['#tools'],
    ];
    $element['#attached']['drupalSettings']['editorjs'][$element['#id']] = $settings;

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function preRenderHidden($element) {
    Element::setAttributes($element, ['id']);

    return parent::preRenderHidden($element);
  }

  /**
   * Attach libraries from default theme to editorjs element.
   *
   * @param array $variables
   *   The renderable array.
   * @param bool $isView
   *   The tool IDs position indicator.
   */
  public static function attachLibraries(array &$variables, bool $isView = FALSE): void {
    /** @var \Drupal\Core\Plugin\DefaultPluginManager $toolsManager */
    $toolsManager = \Drupal::service('plugin.manager.editorjs_tools');

    if ($isView) {
      $toolIds = $variables['tools'];
    }
    else {
      $toolIds = array_keys($variables['#tools'] ?? []);
    }

    foreach ($toolIds as $toolId) {
      /** @var \Drupal\editorjs\Plugin\EditorJsTools\EditorJsToolsInterface $tool */
      $tool = $toolsManager->createInstance($toolId);

      if ($isView) {
        $libraries = $tool->getViewLibraries();
      }
      else {
        $libraries = $tool->getWidgetLibraries();
      }

      $variables['#attached']['library'] = NestedArray::mergeDeep($variables['#attached']['library'] ?? [], $libraries);
    }

    $themes = [
      \Drupal::config('system.theme')->get('default'),
      // @todo How theme libraries need attach to editor?
      // \Drupal::theme()->getActiveTheme()->getName(),
    ];

    foreach ($themes as $theme) {
      if (empty($theme)) {
        continue;
      }

      $themeInfo = \Drupal::service('extension.list.theme')
        ->getExtensionInfo($theme);

      if (empty($themeInfo['editorjs_libraries'])) {
        continue;
      }

      foreach ($themeInfo['editorjs_libraries'] as $editorjsLibrary) {
        $variables['#attached']['library'][] = $editorjsLibrary;
      }
    }
  }

}
