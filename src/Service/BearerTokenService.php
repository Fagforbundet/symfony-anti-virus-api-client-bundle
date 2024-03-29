<?php

namespace Fagforbundet\AntiVirusApiClientBundle\Service;

use Fagforbundet\AntiVirusApiClientBundle\Exception\UnauthorizedException;
use HalloVerden\Oidc\ClientBundle\Entity\Grant\ClientCredentialsGrant;
use HalloVerden\Oidc\ClientBundle\Exception\InvalidTokenException;
use HalloVerden\Oidc\ClientBundle\Exception\ProviderException;
use HalloVerden\Oidc\ClientBundle\Interfaces\OidcRawTokenInterface;
use HalloVerden\Oidc\ClientBundle\Interfaces\OpenIdProviderServiceInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class BearerTokenService implements BearerTokenServiceInterface {
  public const SCOPE = 'antivirus.post:/v1/scan';
  public const CACHE_KEY = 'av_api_client_bearer_token';

  /**
   * BearerTokenService constructor.
   */
  public function __construct(
    private readonly OpenIdProviderServiceInterface $openIdProviderService,
    private readonly CacheInterface $cache = new ArrayAdapter(),
    private readonly string $cacheKey = self::CACHE_KEY,
    private readonly ?string $scope = self::SCOPE,
  ) {
  }

  /**
   * @inheritDoc
   * @throws InvalidArgumentException
   */
  public function getBearerToken(): string {
    return $this->cache->get($this->cacheKey, $this->_getBearerToken(...));
  }

  /**
   * @param ItemInterface $item
   *
   * @return string
   * @throws UnauthorizedException
   */
  private function _getBearerToken(ItemInterface $item): string {
    try {
      $accessToken = $this->openIdProviderService->getTokenResponse(new ClientCredentialsGrant(\explode(' ', $this->scope)))->getAccessToken();
    } catch (InvalidTokenException|ProviderException $e) {
      throw new UnauthorizedException(previous: $e);
    }

    if (!$accessToken instanceof OidcRawTokenInterface) {
      throw new \LogicException(sprintf('$accessToken is not instance of %s', OidcRawTokenInterface::class));
    }

    $item->expiresAfter(\max($accessToken->getExp() - time() - 300, 0));

    return $accessToken->getRawToken();
  }

}
