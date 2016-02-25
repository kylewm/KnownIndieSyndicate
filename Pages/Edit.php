<?php
namespace IdnoPlugins\IndieSyndicate\Pages;

use Idno\Core\Idno;

class Edit extends \Idno\Common\Page {

    function postContent()
    {
        $this->gatekeeper();

        $user = Idno::site()->session()->currentUser();
        $url = $this->getInput('url');

        if ($url) {
            foreach (['name', 'icon', 'style'] as $key) {
                $value = $this->getInput($key);
                if ($value) {
                    $user->indiesyndicate[$url][$key] = $value;
                }
            }
        }

        $user->save();

        $this->forward(Idno::site()->config()->getDisplayURL().'account/indiesyndicate');
    }

}
