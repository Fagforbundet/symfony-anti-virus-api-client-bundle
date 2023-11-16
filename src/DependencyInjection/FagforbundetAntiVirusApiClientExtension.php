<?php

namespace Fagforbundet\AntiVirusApiClientBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class FagforbundetAntiVirusApiClientExtension extends ConfigurableExtension implements PrependExtensionInterface {
  private const BEARER_TOKEN_SERVICE_ID = 'fagforbundet.anti_virus_api.bearer_token_service';
  private const ANTI_VIRUS_API_CLIENT_SERVICE_ID = 'fagforbundet.anti_virus_api.client';
  private const ANTI_VIRUS_API_HTTP_CLIENT_SERVICE_ID = 'fagforbundet.anti_virus_api.http_client';

  /**
   * @inheritDoc
   * @throws \Exception
   */
  protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void {
    $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
    $loader->load('services.yaml');

    if (!isset($mergedConfig['oidc_client'])) {
      $container->removeDefinition(self::BEARER_TOKEN_SERVICE_ID);
      $container->removeDefinition(self::ANTI_VIRUS_API_CLIENT_SERVICE_ID);
      return;
    }

    $bearerTokenService = $container->getDefinition(self::BEARER_TOKEN_SERVICE_ID)
      ->setArgument('$openIdProviderService', new Reference($mergedConfig['oidc_client']));

    $container->getDefinition(self::ANTI_VIRUS_API_CLIENT_SERVICE_ID)
      ->setArgument('$client', new Reference(self::ANTI_VIRUS_API_HTTP_CLIENT_SERVICE_ID));

    if ($mergedConfig['cache']) {
      $bearerTokenService->setArgument('$cache', new Reference($mergedConfig['cache']));
    }
  }

  /**
   * @inheritDoc
   */
  public function prepend(ContainerBuilder $container) {
    $configs = $container->getExtensionConfig($this->getAlias());
    $config = $this->processConfiguration(new Configuration(), $configs);

    $container->prependExtensionConfig('framework', [
      'http_client' => [
        'scoped_clients' => [
          self::ANTI_VIRUS_API_HTTP_CLIENT_SERVICE_ID => [
            'base_uri' => $config['base_url']
          ]
        ]
      ]
    ]);
  }

}
