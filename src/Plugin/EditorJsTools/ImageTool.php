<?php

namespace Drupal\editorjs\Plugin\EditorJsTools;

use Drupal\Component\Utility\Environment;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Url;
use Drupal\file\Plugin\Field\FieldType\FileItem;

/**
 * Plugin implementation of the editorjs_tools.
 *
 * @EditorJsTools(
 *   id = "image",
 *   implementer = "ImageTool",
 *   label = @Translation("Image"),
 *   description = @Translation("Provides image tool."),
 * )
 */
class ImageTool extends EditorJsToolsPluginBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(FieldDefinitionInterface $definition, array $settings = []): array {
    $elements['config'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];

    $elements['config']['endpoints'] = [
      '#type' => 'container',
    ];

    $elements['config']['endpoints']['byFile'] = [
      '#type' => 'textfield',
      '#disabled' => TRUE,
      '#title' => $this->t('Endpoint for upload file'),
      '#default_value' => Url::fromRoute(route_name: 'editorjs.image.file')->toString(),
    ];

    $elements['config']['endpoints']['byUrl'] = [
      '#type' => 'textfield',
      '#disabled' => TRUE,
      '#title' => $this->t('Endpoint for upload file by URL'),
      '#default_value' => Url::fromRoute(route_name: 'editorjs.image.url')->toString(),
    ];

    $elements['config']['additionalRequestData'] = [
      '#type' => 'container',
    ];

    $requestSettings = $settings['config']['additionalRequestData'] ?? [];

    $requestElements['field_id'] = [
      '#type' => 'hidden',
      '#default_value' => $definition->id(),
    ];

    $extensions = str_replace(' ', ', ', $requestSettings['file_extensions'] ?? 'png gif jpg jpeg');
    $requestElements['file_extensions'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Allowed file extensions'),
      '#default_value' => $extensions,
      '#description' => $this->t("Separate extensions with a comma or space. Each extension can contain alphanumeric characters, '.', and '_', and should start and end with an alphanumeric character."),
      '#element_validate' => [[FileItem::class, 'validateExtensions']],
      '#maxlength' => 256,
      '#required' => TRUE,
    ];

    $requestElements['file_directory'] = [
      '#type' => 'textfield',
      '#title' => $this->t('File directory'),
      '#default_value' => $requestSettings['file_directory'] ?? '[date:custom:Y]-[date:custom:m]',
      '#description' => $this->t('Optional subdirectory within the upload destination where files will be stored. Do not include preceding or trailing slashes.'),
      '#element_validate' => [[FileItem::class, 'validateDirectory']],
    ];

    $requestElements['max_filesize'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Maximum upload size'),
      '#default_value' => $requestSettings['max_filesize'] ?? NULL,
      '#description' => $this->t('Enter a value like "512" (bytes), "80 KB" (kilobytes) or "50 MB" (megabytes) in order to restrict the allowed file size. If left empty the file sizes could be limited only by PHP\'s maximum post and file upload sizes (current limit <strong>%limit</strong>).', ['%limit' => format_size(Environment::getUploadMaxSize())]),
      '#size' => 10,
      '#element_validate' => [[FileItem::class, 'validateMaxFilesize']],
      '#weight' => 5,
    ];

    $elements['config']['additionalRequestData'] += $requestElements;

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetLibraries(): array {
    return ['editorjs/image.widget'];
  }

  /**
   * {@inheritdoc}
   */
  public function getViewLibraries(): array {
    return ['editorjs/image.view'];
  }

}
