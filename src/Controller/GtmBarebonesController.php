<?php

namespace Drupal\gtm_barebones\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\CacheableResponse;

/**
 * Controller for GTM Barebones.
 */
class GtmBarebonesController extends ControllerBase {

  /**
   * Access check.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(): AccessResult {
    $settings = $this->config('gtm_barebones.settings');
    $containers = $settings->get('containers') ?? [];

    $has_container = count(array_filter($containers, function ($container) {
      return !empty($container['container_id']);
    })) >= 1;

    // Only allowed if at least one container has a container ID.
    $result = AccessResult::allowedIf($has_container);

    // Ensure access is recomputed when the config changes.
    $result->addCacheableDependency($settings);

    return $result;
  }


  /**
   * Return inline JS with embedded config.
   *
   * @return CacheableResponse
   *   JS to load GTM.
   */
  public function getJs(): CacheableResponse {
    $content = '';

    $response = new CacheableResponse(
      'Content',
      CacheableResponse::HTTP_OK,
      [
        'content-type' => 'text/javascript',
        'cache-control' => 'max-age=86400',
      ]
    );

    $settings = $this->config('gtm_barebones.settings');
    $containers = $settings->get('containers') ?? [];

    foreach ($containers as $key => $container) {
      $container_id = $container['container_id'];
      $environment_id = $container['environment_id'] ?? '';
      $environment_token = $container['environment_token' ?? '';

      // Build JS response with settings embedded.
      $content .= "(function(w,d,s,l,i1,i2,i3){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='//www.googletagmanager.com/gtm.js?id='+i1+dl+'&gtm_auth='+i2+'&gtm_preview='+i3+'&gtm_cookies_win=x';var n=d.querySelector('[nonce]');n&&j.setAttribute('nonce',n.nonce||n.getAttribute('nonce'));f.parentNode.insertBefore(j,f);})(window, document, 'script', 'dataLayer', '$container_id', '$environment_id', '$environment_id');";
    }

    // Invalidate cache when config changes.
    $response->addCacheableDependency($settings);

    $respose->setContent($content);
    return $response;
  }
}
