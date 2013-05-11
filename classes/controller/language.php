<?php defined('SYSPATH') or die('No direct script access.');


class Controller_Language extends Controller_Application {


    /**
     * Action for language change
     */
    public function action_change() {

        $language = $this->request->param('lang');

        Language::set_language($language);

        $this->request->redirect($this->request->referrer());
    }

}
