<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\tao\helpers;

use oat\tao\helpers\Template;
use oat\tao\model\menu\Icon;
use oat\tao\model\ThemeRegistry;
use oat\tao\model\theme\ThemeService;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\layout\AmdLoader;

class Layout{


    /**
     * Compute the parameters for the release message
     *
     * @return array
     */
    public static function getReleaseMsgData(){
        $params = array(
            'version-type' => '',
            'is-unstable'  => self::isUnstable(),
            'is-sandbox'   => false,
            'logo'         => self::getLogoUrl(),
            'link'         => self::getLinkUrl(),
            'msg'          => self::getMessage()
        );

        switch(TAO_RELEASE_STATUS){
            case 'alpha':
            case 'demoA':
                $params['version-type'] = __('Alpha version');
                break;

            case 'beta':
            case 'demoB':
                $params['version-type'] = __('Beta version');
                break;

            case 'demoS':
                $params['version-type'] = __('Demo Sandbox');
                $params['is-sandbox']    = true;
                break;
        }

        return $params;
    }


    /**
     * Compute the expiration time for the sandbox version
     *
     * @return string
     */
    public static function getSandboxExpiration(){
        $datetime   = new \DateTime();
        $d          = new \DateTime($datetime->format('Y-m-d'));
        $weekday    = $d->format('w');
        $weekNumber = $d->format('W');
        $diff       = $weekNumber % 2 ? 7 : 6 - $weekday;
        $d->modify(sprintf('+ %d day', $diff));
        return \tao_helpers_Date::displayInterval($d, \tao_helpers_Date::FORMAT_INTERVAL_LONG);
    }

    /**
     * $icon defined in oat\tao\model\menu\Perspective::fromSimpleXMLElement
     *
     * $icon has two methods, getSource() and getId().
     * There are three possible ways to include icons, either as font, img or svg (not yet supported).
     * - Font uses source to address the style sheet (TAO font as default) and id to build the class name
     * - Img uses source only
     * - Svg uses source to address an SVG sprite and id to point to the right icon in there
     *
     * @param Icon $icon
     * @param string $defaultIcon e.g. icon-extension | icon-action
     * @return string icon as html
     */
    public static function renderIcon($icon, $defaultIcon) {

        $srcExt   = '';
        $isBase64 = false;
		$iconClass = $defaultIcon;
		if(!is_null($icon)){

            if($icon->getSource()) {
                $imgXts   = 'png|jpg|jpe|jpeg|gif|svg';
                $regExp   = sprintf('~((^data:image/(%s))|(\.(%s)$))~', $imgXts, $imgXts);
                $srcExt   = preg_match($regExp, $icon->getSource(), $matches) ? array_pop($matches) : array();
                $isBase64 = 0 === strpos($icon->getSource(), 'data:image');
            }

            $iconClass = $icon->getId() ? $icon->getId() : $defaultIcon;
        }
        // clarification icon vs. glyph: same thing but due to certain CSS rules a second class is required
        switch($srcExt) {
            case 'png':
            case 'jpg':
            case 'jpe':
            case 'jpeg':
            case 'gif':
                return $isBase64
                    ? '<img src="' . $icon->getSource() . '" alt="" class="glyph" />'
                    : '<img src="' . Template::img($icon->getSource(), $icon->getExtension()) . '" alt="" class="glyph" />';
                break;

            case 'svg':
                return sprintf(
                    '<svg class="svg-glyph"><use xlink:href="%s#%s"/></svg>',
                    Template::img($icon->getSource(), $icon->getExtension()),
                    $icon->getId()
                );

            case ''; // no source means an icon font is used
                return sprintf('<span class="%s glyph"></span>', $iconClass);
        }
    }

    /**
     * Create the AMD loader for the current context.
     * It will load login's modules for anonymous session.
     * Loads the bundle mode in production and the dynamic mode in debug.
     *
     * @param string $bundle the bundle URL
     * @param string $controller the controller module id
     * @param array  $params additional parameters
     * @return string the script tag
     */
    public static function getAmdLoader($bundle = null, $controller = null, $params = null){

        $bundleMode   = \tao_helpers_Mode::is('production');
        $configUrl    = get_data('client_config_url');
        $requireJsUrl = Template::js('lib/require.js', 'tao');
        $bootstrapUrl = Template::js('loader/bootstrap.js', 'tao');

        $loader = new AmdLoader($configUrl, $requireJsUrl, $bootstrapUrl);

        if(\common_session_SessionManager::isAnonymous()) {
            $controller = 'controller/login';
            $bundle = Template::js('loader/login.min.js', 'tao');
        }

        if($bundleMode){
            return $loader->getBundleLoader($bundle, $controller, $params);
        }

        return $loader->getDynamicLoader($controller, $params);
    }

    /**
     * @return string
     */
    public static function getTitle() {
        $title = get_data('title');
        return $title ? $title : PRODUCT_NAME . ' ' .  TAO_VERSION;
    }


    /**
     * Navigation is considered small when it has no main and max. 2 item in the settings menu
     * @return bool
     */
    public static function isSmallNavi() {
        $settingsMenu = get_data('settings-menu');
        return empty(get_data('main-menu')) && empty($settingsMenu) || count($settingsMenu) < 3;
    }


    /**
     * Retrieve the template with the actual content
     *
     * @return array
     */
    public static function getContentTemplate() {
        $templateData = (array)get_data('content-template');
        $contentTemplate['path'] = $templateData[0];
        $contentTemplate['ext']  = $templateData[1] ? $templateData[1] : 'tao';
        return $contentTemplate;
    }
    
    /**
     * Get the logo URL.
     * 
     * @return string The absolute URL to the logo image.
     */
    public static function getLogoUrl() {
        $logoFile = Template::img('tao-logo.png', 'tao');

        switch (TAO_RELEASE_STATUS) {
            case 'alpha':
            case 'demoA':
                $logoFile = Template::img('tao-logo-alpha.png', 'tao');
                break;
            case 'beta':
            case 'demoB':
                $logoFile = Template::img('tao-logo-beta.png', 'tao');
                break;
        }
        
        return $logoFile;
    }

    /**
     * Deprecated way to insert a theming css, use custom template instead
     *
     * @deprecated
     * @return string
     */
    public static function getBranding() {
        return 'TAO';
    }

    /**
     * Deprecated way to insert a theming css, use custom template instead
     * 
     * @deprecated
     * @return string
     */
    public static function getThemeUrl() {
        return '';
    }

    public static function getLinkUrl() {
        
        $link = 'http://taotesting.com';
        //move this into the standard template setData()
        switch (TAO_RELEASE_STATUS) {
            case 'alpha':
            case 'demoA':
            case 'beta':
            case 'demoB':
                $link = 'https://forum.taocloud.org/';
                break;
        }

        return $link;
    }

    public static function getMessage() {
        
        $message = '';
        switch (TAO_RELEASE_STATUS) {
            case 'alpha':
            case 'demoA':
            case 'beta':
            case 'demoB':
                $message = __('Please report bugs, ideas, comments or feedback on the TAO Forum');
                break;
        }
        return $message;
    }
    
    public static function isUnstable() {

        $isUnstable = true;
        switch (TAO_RELEASE_STATUS) {
            case 'demoS':
            case 'stable':
                $isUnstable = false;
                break;
        }
        return $isUnstable;
    }

    /**
     *
     * @deprecated use custom template instead
     * @return type
     */
    public static function getLoginMessage() {
        return __("Connect to the TAO platform");
    }

    /**
     *
     * @deprecated change default language if you want to change the "Login" translation
     * @return type
     */
    public static function getLoginLabel() {
        return __("Login");
    }

    /**
     *
     * @deprecated change default language if you want to change the "Password" translation
     * @return type
     */
    public static function getPasswordLabel() {
        return __("Password");
    }

    /**
     *
     * @deprecated use custom footer.tpl template instead
     * @return type
     */
    public static function getCopyrightNotice() {
        return '';
    }
    
    /**
     * Render a themable template identified by its id
     * 
     * @param string $templateId
     * @param array $data
     * @return string
     */
    public static function renderThemeTemplate($target, $templateId, $data = array()){
        
        //search in the registry to get the custom template to render
        $tpl = self::getThemeTemplate($target, $templateId);

        if(!is_null($tpl)){
            //render the template
            $renderer = new \Renderer($tpl, $data);
            return $renderer->render();
        }
        return '';
    }
    
    /**
     * Returns the absolute path of the template to be rendered considering the given context
     *
     * @param string target
     * @param string $templateId
     */
    public static function getThemeTemplate($target, $templateId)
    {
        $service = ServiceManager::getServiceManager()->get(ThemeService::SERVICE_ID);
        return $service->getTheme()->getTemplate($templateId, $target);
    }

    /**
     * Returns the absolute path of the theme css that overwrites the base css
     * 
     * @param string target
     */
    public static function getThemeStylesheet($target){
        $service = ServiceManager::getServiceManager()->get(ThemeService::SERVICE_ID);
        return $service->getTheme()->getStylesheet($target);

    }
}
