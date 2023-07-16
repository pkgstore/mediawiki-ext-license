<?php

namespace MediaWiki\Extension\PkgStore;

use MWException;
use OutputPage, Parser, Skin;

/**
 * Class MW_EXT_License
 */
class MW_EXT_License
{

  /**
   * Get license.
   *
   * @param $license
   *
   * @return array
   */
  private static function getLicense($license): array
  {
    $get = MW_EXT_Kernel::getYAML(__DIR__ . '/store/' . $license . '.yml');
    return $get ?? [] ?: [];
  }

  /**
   * Get license title.
   *
   * @param $license
   *
   * @return string
   */
  private static function getLicenseTitle($license): string
  {
    $license = self::getLicense($license) ? self::getLicense($license) : '';
    return $license['title'] ?? '' ?: '';
  }

  /**
   * Get license icon.
   *
   * @param $license
   *
   * @return string
   */
  private static function getLicenseIcon($license): string
  {
    $license = self::getLicense($license) ? self::getLicense($license) : '';
    return $license['icon'] ?? '' ?: '';
  }

  /**
   * Get license content.
   *
   * @param $license
   *
   * @return string
   */
  private static function getLicenseContent($license): string
  {
    $license = self::getLicense($license) ? self::getLicense($license) : '';
    return $license['content'] ?? '' ?: '';
  }

  /**
   * Get license URL.
   *
   * @param $license
   *
   * @return string
   */
  private static function getLicenseURL($license): string
  {
    $license = self::getLicense($license) ? self::getLicense($license) : '';
    return $license['url'] ?? '' ?: '';
  }

  /**
   * Get license rule.
   *
   * @param $license
   * @param $type
   *
   * @return array
   */
  private static function getLicenseRule($license, $type): array
  {
    $license = self::getLicense($license) ? self::getLicense($license) : [];
    return $license['rule'][$type] ?? [] ?: [];
  }

  /**
   * Register tag function.
   *
   * @param Parser $parser
   *
   * @return void
   * @throws MWException
   */
  public static function onParserFirstCallInit(Parser $parser): void
  {
    $parser->setFunctionHook('license', [__CLASS__, 'onRenderTag']);
  }

  /**
   * Render tag function.
   *
   * @param Parser $parser
   * @param string $type
   *
   * @return string|null
   */
  public static function onRenderTag(Parser $parser, string $type = ''): ?string
  {
    // Argument: type.
    $getType = MW_EXT_Kernel::outClear($type ?? '' ?: '');
    $outType = MW_EXT_Kernel::outNormalize($getType);

    // Check license type, set error category.
    if (!self::getLicense($outType)) {
      $parser->addTrackingCategory('mw-license-error-category');

      return null;
    }

    // Get title.
    $getTitle = self::getLicenseTitle($outType);
    $outTitle = $getTitle;

    // Get icon.
    $getIcon = self::getLicenseIcon($outType);
    $outIcon = $getIcon;

    // Get content.
    $getContent = self::getLicenseContent($outType);
    $outContent = empty($getContent) ? '' : '<p>' . MW_EXT_Kernel::getMessageText('license', $getContent) . '</p>';

    // Get URL.
    $getURL = self::getLicenseURL($outType);
    $outURL = empty($getURL) ? '<em>' . $outTitle . '</em>' : '<a href="' . $getURL . '" rel="nofollow" target="_blank"><em>' . $outTitle . '</em></a>';

    // Get description.
    $getDescription = MW_EXT_Kernel::getMessageText('license', 'description');
    $outDescription = $getDescription . ': ' . $outURL;

    // Get permission.
    $getPermission = self::getLicenseRule($outType, 'permission');
    $outPermission = '';

    // Get condition.
    $getCondition = self::getLicenseRule($outType, 'condition');
    $outCondition = '';

    // Get limitation.
    $getLimitation = self::getLicenseRule($outType, 'limitation');
    $outLimitation = '';

    // Loading messages.
    $msgPermissions = MW_EXT_Kernel::getMessageText('license', 'permissions');
    $msgConditions = MW_EXT_Kernel::getMessageText('license', 'conditions');
    $msgLimitations = MW_EXT_Kernel::getMessageText('license', 'limitations');

    // Render permission.
    if ($getPermission) {
      $outPermission = '<div class="mw-license-permissions">';
      $outPermission .= '<div class="mw-license-permissions-title">' . $msgPermissions . '</div>';
      $outPermission .= '<div class="mw-license-permissions-list">';
      $outPermission .= '<ul>';
      foreach ($getPermission as $value) {
        $outPermission .= '<li>' . MW_EXT_Kernel::getMessageText('license', $value) . '</li>';
      }
      $outPermission .= '</ul></div></div>';
    }

    // Render condition.
    if ($getCondition) {
      $outCondition = '<div class="mw-license-conditions">';
      $outCondition .= '<div class="mw-license-conditions-title">' . $msgConditions . '</div>';
      $outCondition .= '<div class="mw-license-conditions-list">';
      $outCondition .= '<ul>';
      foreach ($getCondition as $value) {
        $outCondition .= '<li>' . MW_EXT_Kernel::getMessageText('license', $value) . '</li>';
      }
      $outCondition .= '</ul></div></div>';
    }

    // Render limitation.
    if ($getLimitation) {
      $outLimitation = '<div class="mw-license-limitations">';
      $outLimitation .= '<div class="mw-license-limitations-title">' . $msgLimitations . '</div>';
      $outLimitation .= '<div class="mw-license-limitations-list">';
      $outLimitation .= '<ul>';
      foreach ($getLimitation as $value) {
        $outLimitation .= '<li>' . MW_EXT_Kernel::getMessageText('license', $value) . '</li>';
      }
      $outLimitation .= '</ul></div></div>';
    }

    // Out HTML.
    $outHTML = '<div class="mw-license navigation-not-searchable mw-box"><div class="mw-license-body">';
    $outHTML .= '<div class="mw-license-icon"><div><i class="far fa-copyright"></i><i class="' . $outIcon . '"></i></div></div>';
    $outHTML .= '<div class="mw-license-content">';
    $outHTML .= '<div class="mw-license-title">' . $outTitle . '</div><p>' . $outDescription . '</p>' . $outContent;
    $outHTML .= '<div class="mw-license-rules">' . $outPermission . $outCondition . $outLimitation . '</div>';
    $outHTML .= '</div></div></div>';

    // Out parser.
    return $parser->insertStripItem($outHTML, $parser->getStripState());
  }

  /**
   * Load resource function.
   *
   * @param OutputPage $out
   * @param Skin $skin
   *
   * @return void
   */
  public static function onBeforePageDisplay(OutputPage $out, Skin $skin): void
  {
    $out->addModuleStyles(['ext.mw.license.styles']);
  }
}
