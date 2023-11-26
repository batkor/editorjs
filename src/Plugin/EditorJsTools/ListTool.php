<?php

namespace Drupal\editorjs\Plugin\EditorJsTools;

use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Plugin implementation of the editorjs_tools.
 *
 * @EditorJsTools(
 *   id = "list",
 *   label = @Translation("List"),
 *   description = @Translation("Provides List tool."),
 *   implementer = "List",
 * )
 */
class ListTool extends EditorJsToolsPluginBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(FieldDefinitionInterface $definition, array $settings = []): array {
    $elements['inlineToolbar'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Inline toolbar'),
      '#default_value' => $settings['inlineToolbar'] ?? FALSE,
    ];

    $elements['config'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];

    $elements['config']['defaultStyle'] = [
      '#type' => 'select',
      '#title' => $this->t('Default list style'),
      '#options' => [
        'unordered' => $this->t('Unordered'),
        'ordered' => $this->t('Ordered'),
      ],
      '#required' => TRUE,
      '#default_value' => $settings['config']['defaultStyle'] ?? 'unordered',
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetLibraries(): array {
    return ['editorjs/list.widget'];
  }

  /**
   * {@inheritdoc}
   */
  public function getViewLibraries(): array {
    return ['editorjs/list.view'];
  }

}
