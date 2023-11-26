<?php

namespace Drupal\editorjs\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Bytes;
use Drupal\Component\Utility\Environment;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\File\Exception\FileWriteException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\editorjs\ImageToolFieldSettings;
use Drupal\file\Upload\FormUploadedFile;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Provide upload callbacks for "image" tool routes.
 */
class ImageController implements ContainerInjectionInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $static = new static();

    return $static;
  }

  /**
   * The callback for "editorjs.image.file" route.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   */
  public function uploadFile(Request $request): Response {
    try {
      if (!$request->files->has('image')) {
        throw new BadRequestHttpException('Not found "image" key.');
      }

      self::checkRequireFields($request, [
        'file_extensions',
        'file_directory',
        'max_filesize',
      ]);

      $uploadFile = $request->files->get('image');
      $formUploadedFile = new FormUploadedFile($uploadFile);
      $result = \Drupal::service('file.upload_handler')
        ->handleFileUpload($formUploadedFile, $this->getValidators($request), $this->getDestination($request), FileSystemInterface::EXISTS_RENAME);

      $file = $result->getFile();

      return new JsonResponse([
        'success' => TRUE,
        'file' => [
          'url' => $file->createFileUrl(FALSE),
          'file_id' => $file->id(),
        ],
      ]);
    }
    catch (\Exception $e) {
      \Drupal::logger('editorjs')->error($e->getMessage());

      return new JsonResponse([
        'success' => FALSE,
      ]);
    }

//    file_save_upload()

    return new JsonResponse([
      'success' => TRUE,
    ]);
  }

  /**
   * The callback for "editorjs.image.url" route.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   */
  public function uploadUrl(Request $request) {
    $x=0;
  }

  /**
   * Check fields on request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param array $fields
   *   The check fields list.
   */
  public static function checkRequireFields(Request $request, array $fields): void {
    foreach ($fields as $field) {
      if (!$request->request->has($field)) {
        throw new BadRequestHttpException(sprintf('Not found "%s" key.', $field));
      }
    }
  }

  /**
   * Create validators list from request keys.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return array
   *   The validators list.
   */
  protected function getValidators(Request $request): array {
    $validators = [
      'file_validate_is_image' => [],
    ];
    $validators['file_validate_extensions'] = [$request->get('file_extensions')];
    $maxFilesize = $request->get('max_filesize');

    if (empty($maxFilesize)) {
      $maxFilesize = Environment::getUploadMaxSize();
    }
    else {
      $maxFilesize = min(Bytes::toNumber($maxFilesize), Environment::getUploadMaxSize());
    }

    $validators['file_validate_size'] = [$maxFilesize];

    return $validators;
  }

  /**
   * Returns destination URI from request after prepare.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return string
   *   The destination URI.
   */
  protected function getDestination(Request $request): string {
    $destination = 'public://';
    $destination .= trim($request->get('file_directory'), '\\/');
    $destination = \Drupal::token()->replace($destination);
    $result = \Drupal::service('file_system')->prepareDirectory($destination);

    if (!$result) {
      throw new FileWriteException(sprintf('Dont prepare directory: %s', $destination));
    }

    return $destination;
  }

}
