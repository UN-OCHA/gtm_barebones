<?php

declare(strict_types = 1);

namespace Drupal\gtm_barebones\Hooks;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Render\Markup;
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
