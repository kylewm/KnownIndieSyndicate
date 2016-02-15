<?php
namespace IdnoPlugins\IndieSyndicate\Pages;

use Idno\Core\Idno;
use IndieAuth\Client as IndieAuthClient;

class Callback extends \Idno\Common\Page {

    function getContent()
    {
        $this->gatekeeper();

        $user = Idno::site()->session()->currentUser();
        $code = $this->getInput('code');
        $state = $this->getInput('state');
        $me = $this->getInput('me');

        $token_endpoint = IndieAuthClient::discoverTokenEndpoint($me);
        $micropub_endpoint = IndieAuthClient::discoverMicropubEndpoint($me);
        $hcard = IndieAuthClient::representativeHCard($me);

        $client_id = Idno::site()->config()->getDisplayURL();
        $redirect_uri = Idno::site()->config()->getDisplayURL().'account/indiesyndicate/cb';

        $result = IndieAuthClient::getAccessToken(
            $token_endpoint, $code, $me, $redirect_uri, $client_id, $state);

        if (isset($result['me']) && isset($result['access_token'])) {
            $me = $result['me'];
            $token = $result['access_token'];
            $name = $me;
            if (!empty($hcard['properties']['name'])) {
                $name = $hcard['properties']['name'][0];
            } else {
                $name = $me;
            }

            $user->indiesyndicate[$me]= [
                'name' => $name,
                'access_token' => $token,
                'micropub_endpoint' => $micropub_endpoint,
            ];

            $user->save();

            Idno::site()->session()->addMessage('Successfully authorized ' . $me);
        } else {
            Idno::site()->session()->addErrorMessage('Authorization was declined or failed for ' . $me);
        }

        $this->forward(Idno::site()->config()->getDisplayURL().'account/indiesyndicate');
    }

}