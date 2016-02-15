<?php
namespace IdnoPlugins\IndieSyndicate\Pages;

use Idno\Core\Idno;

class Account extends \Idno\Common\Page {

    function getContent($params = [])
    {
        $this->gatekeeper();

        $user = Idno::site()->session()->currentUser();
        $t = Idno::site()->template();

        $body = $t->__([])->draw('account/indiesyndicate');

        $t->__([
            'title' => 'IndieSyndicate Settings',
            'body' => $body,
        ])->drawPage();
    }


}