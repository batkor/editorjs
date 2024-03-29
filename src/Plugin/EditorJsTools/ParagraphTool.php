<?php

namespace Drupal\editorjs\Plugin\EditorJsTools;

use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Plugin implementation of the editorjs_tools.
 *
 * @EditorJsTools(
 *   id = "paragraph",
 *   label = @Translation("Paragraph"),
 *   description = @Translation("Provides Paragraph tool."),
 * )
 */
class ParagraphTool extends EditorJsToolsPluginBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(FieldDefinitionInterface $definition, array $settings = []): array {
    $elements = parent::settingsForm($definition, $settings);

    $elements['placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Placeholder'),
      '#description' => $this->t('The placeholder. Will be shown only in the first paragraph when the whole editor is empty.'),
      '#default_value' => $settings['placeholder'] ?? $this->t('Enter a text'),
    ];

    $elements['preserveBlank'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Preserve blank'),
      '#description' => $this->t('Whether or not to keep blank paragraphs when saving editor data.'),
      '#default_value' => $settings['preserveBlank'] ?? FALSE,
    ];

    return $elements;
  }

}
