<?php

namespace Drupal\editorjs\Plugin\EditorJsTools;

use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Plugin implementation of the editorjs_tools.
 *
 * @EditorJsTools(
 *   id = "footnotes",
 *   implementer = "FootnotesTune",
 *   label = @Translation("Footnotes"),
 *   description = @Translation("Provides footnotes tool."),
 *   tune = TRUE,
 *   register_theme = FALSE,
 * )
 */
class FootnotesTool extends EditorJsToolsPluginBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(FieldDefinitionInterface $definition, array $settings = []): array {
    $elements['placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Placeholder'),
      '#default_value' => $settings['placeholder'] ?? '',
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetLibraries(): array {
    return ['editorjs/footnotes.widget'];
  }

  /**
   * {@inheritdoc}
   */
  public function getViewLibraries(): array {
    return ['editorjs/footnotes.view'];
  }

}
