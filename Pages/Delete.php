<?php
namespace IdnoPlugins\IndieSyndicate\Pages;

use Idno\Core\Idno;

class Delete extends \Idno\Common\Page {

    function postContent()
    {
        $this->gatekeeper();

        $user = Idno::site()->session()->currentUser();
        $url = $this->getInput('url');

        if ($url) {
            unset($user->indiesyndicate[$url]);
            $user->save();
        }

        $this->forward(Idno::site()->config()->getDisplayURL().'account/indiesyndicate');
    }

}
