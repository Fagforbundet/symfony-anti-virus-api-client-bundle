<?php

namespace Fagforbundet\AntiVirusApiClientBundle\Exception;

class ScanException extends \Exception implements ExceptionInterface {
  private ?string $error;

  /**
   * ScanException constructor.
   */
  public function __construct(?string $error, string $message = "", int $code = 0, ?\Throwable $previous = null) {
    parent::__construct($message, $code, $previous);
    $this->error = $error;
  }

  /**
   * @return string|null
   */
  public function getError(): ?string {
    return $this->error;
  }

}
