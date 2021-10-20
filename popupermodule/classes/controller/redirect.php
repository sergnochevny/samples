<?php

/**
 * Class Controller_Redirect
 *
 */
class Controller_Redirect extends Controller_Template
{
    /**
     * @var string
     */
    public $template = 'popups/redirect_js';

    /**
     *
     */
    public function before()
    {
        parent::before();

        $this->request->headers['Content-Type'] = 'application/javascript';
    }


    /**
     * @return void
     */
    public function action_get()
    {
        return;
    }

}
