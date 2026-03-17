<?php

declare(strict_types=1);

namespace Drupal\gtm_barebones\Hooks;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\AdminContext;

/**
 * Hooks.
 */
final class GtmBarebonesHooks {

  /**
   * Constructor.
   */
  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
    protected AdminContext $adminContext,
  ) {
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
    $containers = $settings->get('containers');
    if (empty($containers)) {
      return;
    }

    // Cycle through each defined container.
    foreach ($containers as $key => $container) {
      $container_id = $container['container_id'];
      $environment_id = $container['environment_id'] ?? '';
      $environment_token = $container['environment_token'] ?? '';

      if (empty($container_id)) {
        continue;
      }

      $page_top['gtm_barebones_' . $key . '_gtm_noscript_tag'] = [
        '#type' => 'inline_template',
        '#template' => <<<TEMPLATE
          <noscript>
          <iframe src="https://www.googletagmanager.com/ns.html?id={{ container_id }}&gtm_auth={{ environment_token }}&gtm_preview={{ environment_id }}&gtm_cookies_win=x" height="0" width="0" style="display:none;visibility:hidden"></iframe>
          </noscript>
          TEMPLATE,
        '#context' => [
          'container_id' => $container_id,
          'environment_token' => $environment_token,
          'environment_id' => $environment_id,
        ],
        '#cache' => [
          'contexts' => $settings->getCacheContexts(),
          'tags' => $settings->getCacheTags(),
          'max-age' => $settings->getCacheMaxAge(),
        ],
      ];
    }
  }

  /**
   * Check if the current route is an admin route.
   *
   * @return bool
   *   True if the current route is an admin route, false otherwise.
   */
  private function isExcluding(): bool {
    return $this->adminContext->isAdminRoute() === TRUE;
  }

}
