<?php

declare(strict_types = 1);

namespace Drupal\gtm_barebones\Hooks;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Routing\AdminContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Hooks.
 */
final class GtmBarebonesHooks implements ContainerInjectionInterface {

  /**
   * Constructor.
   */
  private function __construct(
    private readonly ConfigFactoryInterface $configFactory,
    protected AdminContext $adminContext,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  final public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('config.factory'),
      $container->get('router.admin_context'),
    );
  }

  /**
   * Implements hook_page_attachments().
   *
   * @see https://developers.google.com/tag-manager/quickstart
   * @see gtm_barebones_page_attachments()
   */
  public function pageAttachments(array &$attachments): void {
    if (TRUE === $this->isExcluding()) {
      return;
    }

    $attachments['#attached']['library'][] = 'gtm_barebones/gtm';

    // Cacheability as exclusions vary.
    (new CacheableMetadata())
      ->addCacheContexts(['route'])
      ->applyTo($attachments);
  }

  /**
   * Implements hook_page_top().
   *
   * @see gtm_barebones_page_top()
   */
  public function pageTop(array &$page_top): void {
    if (TRUE === $this->isExcluding()) {
      return;
    }

    $settings = $this->configFactory->get('gtm_barebones.settings');
    $containerId = $settings->get('container_id');
    if (NULL === $containerId) {
      return;
    }

    $environmentId = $settings->get('environment_id');
    $environmentToken = $settings->get('environment_token');

    $page_top['gtm_barebones_gtm_noscript_tag'] = [
      '#type' => 'inline_template',
      '#template' => <<<TEMPLATE
        <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id={{ containerId }}&gtm_auth={{ environmentToken }}&gtm_preview={{ environmentId }}&gtm_cookies_win=x" height="0" width="0" style="display:none;visibility:hidden"></iframe>
        </noscript>
        TEMPLATE,
      '#context' => [
        'containerId' => $containerId,
        'environmentToken' => $environmentToken,
        'environmentId' => $environmentId,
      ],
    ];
  }

  private function isExcluding(): bool {
    return $this->adminContext->isAdminRoute() === TRUE;
  }

}
