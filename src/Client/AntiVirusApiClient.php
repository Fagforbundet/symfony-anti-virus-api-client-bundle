<?php

namespace Fagforbundet\AntiVirusApiClientBundle\Client;

use Fagforbundet\AntiVirusApiClientBundle\Entity\Response\FileScanResponse;
use Fagforbundet\AntiVirusApiClientBundle\Exception\FileToLargeException;
use Fagforbundet\AntiVirusApiClientBundle\Exception\ForbiddenException;
use Fagforbundet\AntiVirusApiClientBundle\Exception\ScanException;
use Fagforbundet\AntiVirusApiClientBundle\Exception\UnauthorizedException;
use Fagforbundet\AntiVirusApiClientBundle\Exception\UnknownException;
use Fagforbundet\AntiVirusApiClientBundle\Service\BearerTokenServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AntiVirusApiClient implements AntiVirusApiClientInterface {
  private const SCAN_PATH = '/v1/scan';

  private const ERROR_AV_FILE_SIZE_TOO_LARGE = 'AV_FILE_SIZE_TOO_LARGE';

  /**
   * AntiVirusClient constructor.
   */
  public function __construct(
    private readonly HttpClientInterface $client,
    private readonly BearerTokenServiceInterface $bearerTokenService
  ) {
  }

  /**
   * @inheritDoc
   */
  public function scan($file, ?string $filename = null): FileScanResponse {
    if (!is_resource($file) && !\is_string($file) && !$file instanceof File) {
      throw new \InvalidArgumentException(\sprintf('$file is not a resource, string or "%s". It is "%s"', File::class, \get_debug_type($file)));
    }

    if (null === $filename && !$file instanceof File) {
      $filename = 'tmp'; // anti-virus API requires a filename.
    }

    $formData = new FormDataPart([
      'file' => new DataPart($file, $filename)
    ]);

    try {
      $response = $this->client->request(Request::METHOD_POST, self::SCAN_PATH, [
        'headers' => $formData->getPreparedHeaders()->toArray(),
        'body' => $formData->bodyToIterable(),
        'auth_bearer' => $this->bearerTokenService->getBearerToken(),
      ]);
      $content = $response->toArray();
    } catch (ClientExceptionInterface $e) {
      switch ($e->getCode()) {
        case Response::HTTP_REQUEST_ENTITY_TOO_LARGE:
          throw new FileToLargeException(previous: $e);
        case Response::HTTP_UNAUTHORIZED:
          throw new UnauthorizedException(previous: $e);
        case Response::HTTP_FORBIDDEN:
          throw new ForbiddenException(previous: $e);
      }

      try {
        /** @noinspection PhpUnhandledExceptionInspection */
        $content = $e->getResponse()->toArray(false);
      } catch (DecodingExceptionInterface) {
        throw new UnknownException(previous: $e);
      }

      if (($content['error'] ?? null) === self::ERROR_AV_FILE_SIZE_TOO_LARGE) {
        throw new FileToLargeException($content['message'] ?? '', previous: $e);
      }

      throw new ScanException($content['error'] ?? null, $content['message'] ?? '', previous: $e);
    } catch (ExceptionInterface $e) {
      throw new UnknownException(previous: $e);
    }

    return FileScanResponse::createFromResponseArray($content);
  }

}
