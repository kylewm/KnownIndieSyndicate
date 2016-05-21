<?php
namespace IdnoPlugins\IndieSyndicate\Pages;

use Idno\Core\Idno;
use Idno\Core\MentionClient;
use Idno\Core\Webservice;
use IndieAuth\Client as IndieAuthClient;

class Add extends \Idno\Common\Page {


    function postContent()
    {
        $this->gatekeeper();

        $user = Idno::site()->session()->currentUser();
        $url = $this->getInput('url');

        if ($micropub_endpoint = IndieAuthClient::discoverMicropubEndpoint($url)) {
            $auth_endpoint = IndieAuthClient::discoverAuthorizationEndpoint($url);
            $client_id = Idno::site()->config()->getDisplayURL();
            $redirect_uri = Idno::site()->config()->getDisplayURL().'account/indiesyndicate/cb';

            $auth_url = IndieAuthClient::buildAuthorizationURL(
                $auth_endpoint, $url, $redirect_uri, $client_id, 'TODO123', 'post');

            $this->forward($auth_url);
        } else {
            $mention_client = new MentionClient();
            if ($webmention_endpoint = $mention_client->discoverWebmentionEndpoint($url)) {
                $name = $this->parseTitle($url);
                $user->indiesyndicate[$url] = [
                    'name' => $name ? $name : $url,
                    'method' => 'webmention',
                ];
                $user->save();
                Idno::site()->session()->addMessage('Added webmention target ' . $me);
            } else {
                Idno::site()->session()->addMessage('Endpoint does not appear to support Micropub or Webmention: ' . $me);
            }

            $this->forward(Idno::site()->config()->getDisplayURL().'account/indiesyndicate');
        }
    }

    function parseTitle($url)
    {
        $response = Webservice::get($url);
        if ($response['response'] == 200) {
            $doc = \DOMDocument::loadHTML($response['content']);
            foreach ($doc->getElementsByTagName('title') as $title) {
                return trim($title->textContent);
            }
        }
        return false;
    }
}