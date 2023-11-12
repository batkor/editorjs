<?php

namespace Drupal\editorjs\Plugin\EditorJsTools;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for editorjs_tools plugins.
 */
abstract class EditorJsToolsPluginBase extends PluginBase implements EditorJsToolsInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The manager of tools.
   */
  protected DefaultPluginManager $toolsManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $static = new static($configuration, $plugin_id, $plugin_definition);
    $static->toolsManager = $container->get('plugin.manager.editorjs_tools');

    return $static;
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function description(): string {
    return (string) $this->pluginDefinition['description'];
  }

  /**
   * {@inheritdoc}
   */
  public function implementer(): string {
    return $this->pluginDefinition['implementer'] ?? '';
  }

  /**
   * {@inheritdoc}
   */
  public function isTune(): bool {
    return $this->pluginDefinition['tune'] ?? FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $settings = []): array {

    $elements['inlineToolbar'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Inline toolbar'),
      '#default_value' => $settings['inlineToolbar'] ?? FALSE,
    ];

    if (!$this->isTune()) {
      $elements['tunes'] = [
        '#type' => 'checkboxes',
        '#weight' => 50,
        '#options' => $this->getTuneOptions(),
        '#title' => $this->t('Include tunes'),
        '#default_value' => $settings['tunes'] ?? [],
      ];
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetLibraries(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getViewLibraries(): array {
    return [];
  }

  /**
   * Returns tune tools list to form element.
   *
   * @return array
   *   The tune tools list.
   */
  protected function getTuneOptions(): array {
    $output = [];
    $tools = $this->toolsManager->getDefinitions();

    foreach ($tools as $id => $definition) {
      if (!empty($definition['tune'])) {
        $output[$id] = $definition['label'];
      }
    }

    return $output;
  }

}
