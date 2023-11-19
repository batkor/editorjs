<?php

namespace Drupal\editorjs\Plugin\EditorJsTools;

/**
 * Plugin implementation of the editorjs_tools.
 *
 * @EditorJsTools(
 *   id = "code",
 *   implementer = "CodeTool",
 *   label = @Translation("Code tool"),
 *   description = @Translation("Provides code tool."),
 * )
 */
class CodeTool extends EditorJsToolsPluginBase implements EditorJsToolsInterface {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $settings = []): array {
    $elements = parent::settingsForm($settings);

    $elements['placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Placeholder'),
      '#default_value' => $settings['placeholder'] ?? $this->t('Enter a code'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetLibraries(): array {
    return ['editorjs/code.widget'];
  }

  /**
   * {@inheritdoc}
   */
  public function getViewLibraries(): array {
    return ['editorjs/code.view'];
  }

}
