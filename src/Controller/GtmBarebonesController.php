<?php

namespace Drupal\gtm_barebones\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

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
    $settings = \Drupal::config('gtm_barebones.settings');
    $containerId = $settings->get('container_id');

    return AccessResult::allowedIf($containerId !== NULL);
  }


  /**
   * Return inline JS with embedded config.
   *
   * @return string
   *   JS to load GTM.
   */
  public function getJs(): Response {
    $settings = \Drupal::config('gtm_barebones.settings');
    $containerId = $settings->get('container_id');
    $environmentId = $settings->get('environment_id');
    $environmentToken = $settings->get('environment_token');

    $response = new Response(
      'Content',
      Response::HTTP_OK,
      ['content-type' => 'text/javascript']
    );

    $response->setContent(<<<JS
      (function(w,d,s,l,i1,i2,i3){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='//www.googletagmanager.com/gtm.js?id='+i1+dl+'&gtm_auth='+i2+'&gtm_preview='+i3+'&gtm_cookies_win=x';var n=d.querySelector('[nonce]');n&&j.setAttribute('nonce',n.nonce||n.getAttribute('nonce'));f.parentNode.insertBefore(j,f);})(window, document, 'script', 'dataLayer', '$containerId', '$environmentToken', '$environmentId');
    JS);

    return $response;
  }
}
