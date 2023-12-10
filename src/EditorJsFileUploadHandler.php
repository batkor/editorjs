<?php

namespace Drupal\editorjs;

use Drupal\Core\File\Event\FileUploadSanitizeNameEvent;
use Drupal\Core\File\Exception\FileExistsException;
use Drupal\Core\File\Exception\FileWriteException;
use Drupal\Core\File\Exception\InvalidStreamWrapperException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Drupal\file\Entity\File;
use Drupal\file\Upload\FileUploadHandler;
use Drupal\file\Upload\FileUploadResult;
use Drupal\file\Upload\FileValidationException;
use Drupal\file\Upload\UploadedFileInterface;
use GuzzleHttp\Client;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mime\MimeTypeGuesserInterface;
use function _PHPStan_0f7d3d695\React\Promise\Stream\first;

/**
 * The file upload manager.
 */
class EditorJsFileUploadHandler {

  /**
   * Constructors.
   */
  public function __construct(
    protected FileUploadHandler $fileUploadHandler,
    protected Client $client,
    protected MimeTypeGuesserInterface $mimeTypeGuesser,
    protected AccountInterface $userProxy,
    protected EventDispatcherInterface $eventDispatcher,
    protected FileSystemInterface $fileSystem,
    protected StreamWrapperManagerInterface $streamWrapperManager
  ) {}

  /**
   * Creates a file from an upload.
   *
   * @param \Drupal\file\Upload\UploadedFileInterface $uploadedFile
   *   The uploaded file object.
   * @param array $validators
   *   The validators to run against the uploaded file.
   * @param string $destination
   *   The destination directory.
   * @param int $replace
   *   Replace behavior when the destination file already exists.
   *
   * @return \Drupal\file\Upload\FileUploadResult
   *   The upload result.
   */
  public function fromUploadedFile(
    UploadedFileInterface $uploadedFile,
    array $validators = [],
    string $destination = 'temporary://',
    int $replace = FileSystemInterface::EXISTS_REPLACE
  ): FileUploadResult {
    return $this
      ->fileUploadHandler
      ->handleFileUpload($uploadedFile, $validators, $destination, $replace);
  }

  /**
   * Upload image by URL and create file entity.
   *
   * @param string $url
   *   The URL to image.
   * @param array $validators
   *   The validators to run against the uploaded file.
   * @param string $destination
   *   The destination directory.
   * @param int $replace
   *   Replace behavior when the destination file already exists.
   *
   * @return \Drupal\file\Upload\FileUploadResult
   *   The upload result.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function fromUrl(string $url, array $validators, string $destination, int $replace): FileUploadResult {
    $response = $this->client->get($url);
    $destinationScheme = $this->streamWrapperManager->getScheme($destination);

    if (!$this->streamWrapperManager->isValidScheme($destinationScheme)) {
      throw new InvalidStreamWrapperException(sprintf('The file could not be uploaded because the destination "%s" is invalid.', $destination));
    }

    $destination = trim($destination, '\\/') . '/';

    $filename = $this->fileSystem->basename($url);
    $extensions = FileUploadHandler::DEFAULT_EXTENSIONS;

    if (!empty($validators['file_validate_extensions'])) {
      $extensions = reset($validators['file_validate_extensions']);
    }

    $event = new FileUploadSanitizeNameEvent($filename, $extensions);
    $this->eventDispatcher->dispatch($event);
    $filename = $event->getFilename();

    $mimeType = $this->mimeTypeGuesser->guessMimeType($filename);
    $destinationFilename = $this->fileSystem->getDestinationFilename($destination . $filename, $replace);

    if ($destinationFilename === FALSE) {
      throw new FileExistsException(sprintf('Destination file "%s" exists', $destinationFilename));
    }

    $fileUri = $this
      ->fileSystem
      ->saveData($response->getBody()->getContents(), $destinationFilename, $replace);

    $file = File::create([
      'uid' => $this->userProxy->id(),
      'status' => 0,
      'uri' => $fileUri,
    ]);
    $nameAfterSave = $this->fileSystem->basename($fileUri);
    $file->setFilename($nameAfterSave);
    $file->setMimeType($mimeType);
    $file->setSize($response->getBody()->getSize());

    $errors = file_validate($file, $validators);

    if (!empty($errors)) {
      throw new FileValidationException('File validation failed', $filename, $errors);
    }

    $result = (new FileUploadResult())
      ->setOriginalFilename($nameAfterSave)
      ->setSanitizedFilename($filename)
      ->setFile($file);

    if ($event->isSecurityRename()) {
      $result->setSecurityRename();
    }

    $this->fileSystem->chmod($file->getFileUri());

    foreach ($file->validate() as $violation) {
      $errors[] = $violation->getMessage();
    }

    if (!empty($errors)) {
      throw new FileValidationException('File validation failed', $filename, $errors);
    }

    $file->save();

    return $result;
  }

}
