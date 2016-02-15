<?php
namespace IdnoPlugins\IndieSyndicate\Pages;

use Idno\Core\Idno;
use IndieAuth\Client as IndieAuthClient;

class Add extends \Idno\Common\Page {


    function postContent()
    {
        $url = $this->getInput('url');
        $auth_endpoint = IndieAuthClient::discoverAuthorizationEndpoint($url);
        $client_id = Idno::site()->config()->getDisplayURL();
        $redirect_uri = Idno::site()->config()->getDisplayURL().'account/indiesyndicate/cb';

        $auth_url = IndieAuthClient::buildAuthorizationURL(
            $auth_endpoint, $url, $redirect_uri, $client_id, 'TODO123', 'post');

        $this->forward($auth_url);
    }

}