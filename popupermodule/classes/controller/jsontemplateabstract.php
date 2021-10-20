<?php

namespace Controller;

use Exception;
use View;

/**
 * Class JsonTemplateAbstract
 *
 * Controller to build response as JSON.
 * Contains array $this->template->result which will
 * be encoded to JSON and will returned in response
 */
abstract class JsonTemplateAbstract extends TemplateControllerAbstract
{
    /**
     * @var View
     */
    public $template = 'popups/template_json';

    /**
     * @var string
     */
    public $_contentType = 'application/json';

    /**
     * Function before
     *
     * @throws Exception
     */
    public function before()
    {
        parent::before();

        $this->template->result = [
            'success' => true,
            'messages' => []
        ];
    }

    /**
     * @throws Exception
     */
    protected function _loadViewTemplate()
    {
        $this->template->content = json_encode(
            isset($this->template->result) ? $this->template->result : ''
        );
    }

}
