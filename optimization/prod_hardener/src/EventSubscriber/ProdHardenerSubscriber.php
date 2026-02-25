<?php

declare(strict_types=1);

namespace Drupal\prod_hardener\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\State\StateInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Enforces prod settings and disables dev modules on prod requests.
 */
final class ProdHardenerSubscriber implements EventSubscriberInterface {

  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
    private readonly ModuleInstallerInterface $moduleInstaller,
    private readonly StateInterface $state,
    private readonly LoggerInterface $logger,
  ) {}

  public static function getSubscribedEvents(): array {
    // Very low priority so other bootstrap logic runs first.
    return [KernelEvents::REQUEST => ['onRequest', -512]];
  }

  public function onRequest(RequestEvent $event): void {
    if ($event->isMainRequest() === FALSE) {
      return;
    }

    // Avoid running on CLI to prevent disabling during drush or cron unexpectedly.
    if (PHP_SAPI === 'cli') {
      return;
    }

    // Run once per cache clear.
    if ($this->state->get('prod_hardener.ran', FALSE)) {
      return;
    }

    $env = $_ENV['DRUPAL_ENV'] ?? NULL;
    $settingsEnv = \Drupal::hasContainer() && \Drupal::service('settings')->get('prod_hardener_env');
    $isProd = ($env === 'prod') || ($settingsEnv === 'prod');

    if (!$isProd) {
      return;
    }

    $config = $this->configFactory->get('prod_hardener.settings');
    $devModules = array_filter(array_map('trim', explode("\n", (string) $config->get('dev_modules') ?? '')));

    if (!empty($devModules)) {
      $installed = \Drupal::service('extension.list.module')->getList();
      $toDisable = array_values(array_intersect(array_keys($installed), $devModules));

      if (!empty($toDisable)) {
        try {
          $this->moduleInstaller->uninstall($toDisable);
          $this->logger->notice('Prod Hardener disabled dev modules: @mods', ['@mods' => implode(', ', $toDisable)]);
        }
        catch (\Throwable $e) {
          $this->logger->error('Prod Hardener failed to uninstall modules: @msg', ['@msg' => $e->getMessage()]);
        }
      }
    }

    // Enforce performance settings via state (read by SettingsForm instructions).
    // You can also choose to write config here, but it's safer to document config export.
    $this->state->set('prod_hardener.ran', TRUE);
  }

}