<?php

namespace Fagforbundet\AntiVirusApiClientBundle\Entity\Response;

final class FileScanResponse {
  private FileScanResult $result;
  private ?string $fileName;
  private ?string $virusName;

  /**
   * FileScanResponse constructor.
   */
  public function __construct(FileScanResult $result, ?string $fileName = null, ?string $virusName = null) {
    $this->result = $result;
    $this->fileName = $fileName;
    $this->virusName = $virusName;
  }

  /**
   * @param array $responseArray
   *
   * @return static
   */
  public static function createFromResponseArray(array $responseArray): self {
    return new self(FileScanResult::from($responseArray['result']), $responseArray['fileName'] ?? null, $responseArray['virusName'] ?? null);
  }

  /**
   * @return FileScanResult
   */
  public function getResult(): FileScanResult {
    return $this->result;
  }

  /**
   * @return string
   */
  public function getFileName(): string {
    return $this->fileName;
  }

  /**
   * @return string|null
   */
  public function getVirusName(): ?string {
    return $this->virusName;
  }

}
