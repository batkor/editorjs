<?php

namespace Drupal\editorjs\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Bytes;
use Drupal\Component\Utility\Environment;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\File\Exception\FileWriteException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Utility\Token;
use Drupal\editorjs\EditorJsFileUploadHandler;
use Drupal\file\Upload\FormUploadedFile;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Provide upload callbacks for "image" tool routes.
 */
class ImageController implements ContainerInjectionInterface {

  /**
   * The "editorjs" log chanel.
   */
  protected ?LoggerChannelInterface $logger;

  /**
   * The file upload handler.
   */
  protected ?EditorJsFileUploadHandler $fileUploadHandler;

  /**
   * The token service.
   */
  protected ?Token $token;

  /**
   * The file system service.
   */
  protected ?FileSystemInterface $fileSystem;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $static = new static();
    $static->fileUploadHandler = $container->get('editorjs.file.upload_handler');
    $static->logger = $container->get('logger.channel.editorjs');
    $static->token = $container->get('token');
    $static->fileSystem = $container->get('file_system');

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

      $data = $request->request->all();
      self::checkRequireFields($data, [
        'file_extensions',
        'file_directory',
        'max_filesize',
      ]);

      $uploadFile = $request->files->get('image');
      $formUploadedFile = new FormUploadedFile($uploadFile);
      $result = $this
        ->fileUploadHandler
        ->fromUploadedFile($formUploadedFile, $this->getValidators($data), $this->getDestination($data), FileSystemInterface::EXISTS_RENAME);

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
      $this->logger->error($e->getMessage());

      return new JsonResponse([
        'success' => FALSE,
      ]);
    }
  }

  /**
   * The callback for "editorjs.image.url" route.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   */
  public function uploadUrl(Request $request): Response {
    try {
      $data = Json::decode($request->getContent());
      self::checkRequireFields($data, [
        'url',
        'file_extensions',
        'file_directory',
        'max_filesize',
      ]);

      $result = $this
        ->fileUploadHandler
        ->fromUrl($data['url'], $this->getValidators($data), $this->getDestination($data), FileSystemInterface::EXISTS_RENAME);

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
      $this->logger->error($e->getMessage());

      return new JsonResponse([
        'success' => FALSE,
      ]);
    }
  }

  /**
   * Check fields on request.
   *
   * @param array $data
   *   The request data.
   * @param array $fields
   *   The check fields list.
   */
  public static function checkRequireFields(array $data, array $fields): void {
    foreach ($fields as $field) {
      if (!array_key_exists($field, $data)) {
        throw new BadRequestHttpException(sprintf('Not found "%s" key.', $field));
      }
    }
  }

  /**
   * Create validators list from request keys.
   *
   * @param array $data
   *   The request data.
   *
   * @return array
   *   The validators list.
   */
  protected function getValidators(array $data): array {
    $validators = [
      'file_validate_is_image' => [],
    ];

    $extensions = preg_replace('/([, ]+\.?)/', ' ', trim(strtolower($data['file_extensions'])));
    $extension_array = array_unique(array_filter(explode(' ', $extensions)));
    $extensions = implode(' ', $extension_array);

    $validators['file_validate_extensions'] = [$extensions];

    $maxFilesize = $data['max_filesize'];

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
   * @param array $data
   *   The request data.
   *
   * @return string
   *   The destination URI.
   */
  protected function getDestination(array $data): string {
    $destination = 'public://';
    $destination .= trim($data['file_directory'], '\\/');
    $destination = $this->token->replace($destination);
    $result = $this->fileSystem->prepareDirectory($destination);

    if (!$result) {
      throw new FileWriteException(sprintf('Dont prepare directory: %s', $destination));
    }

    return $destination;
  }

}
