<?php

namespace Drupal\editorjs\Plugin\EditorJsTools;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Interface for editorjs_tools plugins.
 */
interface EditorJsToolsInterface extends PluginInspectionInterface {

  /**
   * Returns the translated plugin label.
   *
   * @return string
   *   The translated title.
   */
  public function label(): string;

  /**
   * Returns the translated plugin description.
   *
   * @return string
   *   The translated description.
   */
  public function description(): string;

  /**
   * Returns javascript name class/object implements tool.
   *
   * @return string
   *   The translated description.
   */
  public function implementer(): string;

  /**
   * Returns TRUE if tool is a tune else FALSE.
   */
  public function isTune(): bool;

  /**
   * Returns form elements for EditorJs toll settings.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $definition
   *   The field definition.
   * @param array $settings
   *   The saved settings.
   *
   * @return array
   *   The renderable form elements.
   */
  public function settingsForm(FieldDefinitionInterface $definition, array $settings = []): array;

  /**
   * Returns the asset libraries attached to the tool plugin on widget.
   *
   * @return string[]
   *   The libraries list.
   */
  public function getWidgetLibraries(): array;

  /**
   * Returns the asset libraries attached to the tool plugin on view.
   *
   * @return string[]
   *   The libraries list.
   */
  public function getViewLibraries(): array;

}
