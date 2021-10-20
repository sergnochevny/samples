<?php

namespace Controller;

use Exception;
use F;

/**
 * Class JSTemplateAbstract
 *
 * Controller to build response as JS.
 * Contains template to use modal windows(pop-ups).
 *
 * Also it has:
 *  $this->template->htmlContent string. Can contains HTML to show in in model window(pop-up)
 *  $this->template->jsContent string. Can contains JS to execute it after cuurent response appalling
 *  $this->template->scripts array. Can contains list of addresses to JS files
 *      which will be additionally applied ith current script
 *  $this->template->styles array. Can contains list of addresses to CSS files
 *      which will be additionally applied ith current script
 *      true - can not be closed, false - can be closed
 */
abstract class JSTemplateAbstract extends TemplateControllerAbstract
{
    /**
     * @var string
     */
    public $template = 'popups/template_js';

    /**
     * @var string
     */
    public $_contentType = 'application/javascript';

    /**
     * @throws Exception
     */
    protected function _loadViewTemplate()
    {
        $this->template->currentHost = F::getHostName();
        $this->template->popupsLang = $this->_getLanguage();
        $this->template->ip = $this->_getIpAddress();
        $this->template->countryByIP = $this->_getCountryByIp();
        $this->template->ipAddress = $this->_getIpAddress();
    }

}
