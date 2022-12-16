<?php

namespace Fagforbundet\AntiVirusApiClientBundle\Service;

use Fagforbundet\AntiVirusApiClientBundle\Exception\UnauthorizedException;

interface BearerTokenServiceInterface {

  /**
   * @return string
   * @throws UnauthorizedException
   */
  public function getBearerToken(): string;

}
