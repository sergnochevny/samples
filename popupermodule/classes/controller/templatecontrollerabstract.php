<?php

namespace Controller;

use Controller_Template;
use Exception;
use F;
use I18n;
use Kohana_Exception404;
use Popuper\PopupsManager;
use Controller\CollectParameters as CollectParametersTrait;

/**
 * Class TemplateControllerAbstract
 *
 * Build output template layer abstract controller
 *
 * @package Controller
 */
abstract class TemplateControllerAbstract extends Controller_Template
{
    use CollectParametersTrait;

    /** @var string */
    protected $_contentType = '';

    /**
     * @throws Exception
     */
    abstract protected function _loadViewTemplate();

    /**
     * Function before
     *
     * @throws Exception
     */
    public function before()
    {
        if (!PopupsManager::isEnabled()) {
            throw new Kohana_Exception404();
        }

        if (F::IsAjaxMode()) {
            $this->_contentType = 'application/json';
        }

        if ($this->_contentType) {
            $this->request->headers['Content-Type'] = $this->_contentType;
        }

        $this->_loadParameters();

        I18n::lang($this->_getLanguage());

        parent::before();
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function after()
    {
        $this->_updateShownPopupsPerSession();
        $this->_loadViewTemplate();

        parent::after();
    }

}
