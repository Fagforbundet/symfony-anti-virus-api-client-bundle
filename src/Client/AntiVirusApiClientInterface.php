<?php

namespace Fagforbundet\AntiVirusApiClientBundle\Client;

use Fagforbundet\AntiVirusApiClientBundle\Entity\Response\FileScanResponse;
use Fagforbundet\AntiVirusApiClientBundle\Exception\ExceptionInterface;
use Symfony\Component\Mime\Part\File;

interface AntiVirusApiClientInterface {

  /**
   * @param resource|string|File $file
   * @param string|null          $filename
   *
   * @return FileScanResponse
   * @throws ExceptionInterface
   */
  public function scan(mixed $file, ?string $filename = null): FileScanResponse;

}
