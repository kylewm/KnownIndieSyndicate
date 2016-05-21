<?php

namespace IdnoPlugins\IndieSyndicate;

use Idno\Core\Idno;
use Idno\Core\Event;
use Idno\Core\MentionClient;
use Idno\Core\Webservice;

class Main extends \Idno\Common\Plugin {

    function init()
    {
        parent::init();
    }

    function registerPages()
    {
        parent::registerPages();
        Idno::site()->addPageHandler('/account/indiesyndicate/?', '\IdnoPlugins\IndieSyndicate\Pages\Account');
        Idno::site()->addPageHandler('/account/indiesyndicate/add/?', '\IdnoPlugins\IndieSyndicate\Pages\Add');
        Idno::site()->addPageHandler('/account/indiesyndicate/cb/?', '\IdnoPlugins\IndieSyndicate\Pages\Callback');
        Idno::site()->addPageHandler('/account/indiesyndicate/edit/?', '\IdnoPlugins\IndieSyndicate\Pages\Edit');
        Idno::site()->addPageHandler('/account/indiesyndicate/delete/?', '\IdnoPlugins\IndieSyndicate\Pages\Delete');

        Idno::site()->template()->extendTemplate('account/menu/items', 'account/menu/items/indiesyndicate');
    }

    function registerEventHooks()
    {
        parent::registerEventHooks();

        Idno::site()->syndication()->registerService('indiesyndicate', function () {
            return true;
        }, ['note', 'article', 'image', 'like', 'share']);


        Idno::site()->addEventHook('user/auth/success', function (Event $event) {
            $is = (array) Idno::site()->session()->currentUser()->indiesyndicate;
            foreach ($is as $url => $details) {
                Idno::site()->syndication()->registerServiceAccount('indiesyndicate', $url, $details['name'], $details);
            }
        });

        Idno::site()->addEventHook('post/note/indiesyndicate', function (Event $event) {
            $eventdata = $event->data();
            $sa = $eventdata['syndication_account'];
            $object = $eventdata['object'];
            if ($this->doWebmention($sa, $object)) return;

            $details = $this->getAccountDetails($sa);
            $style = isset($details['style']) ? $details['style'] : 'default';
            $params = [
                'h'       => 'entry',
                'content' => $object->body,
                'url'     => $object->getSyndicationURL(),
            ];
            if (is_array($object->inreplyto) && !empty($object->inreplyto)) {
                $params['in-reply-to'] = $object->inreplyto[0];
            }
            $this->doMicropub($sa, $object, $params);
        });

        Idno::site()->addEventHook('post/image/indiesyndicate', function (Event $event) {
            $eventdata = $event->data();
            $sa = $eventdata['syndication_account'];
            $object = $eventdata['object'];
            if ($this->doWebmention($sa, $object)) return;

            $details = $this->getAccountDetails($sa);
            $style = isset($details['style']) ? $details['style'] : 'default';
            if ($style === 'microblog') {
                // combine name and content for twitter

                $content = '';
                if ($object->title) {
                    $content .= $object->title;
                }
                if ($object->body) {
                    if (!empty($content)) { $content .= "\n"; }
                    $content .= trim(strip_tags($object->body));
                }

                $params = [
                    'h' => 'entry',
                    'content' => $content,
                    'url' => $object->getSyndicationURL(),
                ];
            } else {
                $params = [
                    'h' => 'entry',
                    'name' => $object->title,
                    'content' => $object->body,
                    'url' => $object->getSyndicationURL(),
                ];
            }

            foreach ($object->getAttachments() as $attachment) {
                if ($file = \Idno\Entities\File::getByID($attachment['_id'])) {
                    $photofile = tempnam(sys_get_temp_dir(), 'indiesyndicate_photo');
                    $file->write($photofile);
                    $params['photo'] = '@' . $photofile;
                    break;
                }
            }

            $this->doMicropub($sa, $object, $params);

            if (isset($photofile)) {
                unlink($photofile);
            }
        });

        Idno::site()->addEventHook('post/article/indiesyndicate', function (Event $event) {
            $eventdata = $event->data();
            $sa = $eventdata['syndication_account'];
            $object = $eventdata['object'];
            if ($this->doWebmention($sa, $object)) return;

            $details = $this->getAccountDetails($sa);
            $style = isset($details['style']) ? $details['style'] : 'default';

            if ($style === 'microblog') {
                $params = [
                    'h' => 'entry',
                    'content' => $object->title . ': ' . $object->getSyndicationURL(),
                    'url' => $object->getSyndicationURL(),
                ];
            } else {
                $params = [
                    'h' => 'entry',
                    'name' => $object->title,
                    'content' => $object->body,
                    'url' => $object->getSyndicationURL(),
                ];
            }

            $this->doMicropub($sa, $object, $params);
        });

        Idno::site()->addEventHook('post/like/indiesyndicate', function (Event $event) {
            $eventdata = $event->data();
            $sa = $eventdata['syndication_account'];
            $object = $eventdata['object'];
            if ($this->doWebmention($sa, $object)) return;

            $details = $this->getAccountDetails($sa);
            $style = isset($details['style']) ? $details['style'] : 'default';

            $params = [
                'h' => 'entry',
                'like-of' => $object->likeof,
                'url' => $object->getSyndicationURL(),
            ];
            $this->doMicropub($sa, $object, $params);
        });

        Idno::site()->addEventHook('post/repost/indiesyndicate', function (Event $event) {
            $eventdata = $event->data();
            $sa = $eventdata['syndication_account'];
            $object = $eventdata['object'];
            if ($this->doWebmention($sa, $object)) return;

            $details = $this->getAccountDetails($sa);
            $style = isset($details['style']) ? $details['style'] : 'default';

            $params = [
                'h' => 'entry',
                'repost-of' => $object->repostof,
                'url' => $object->getSyndicationURL(),
            ];
            $this->doMicropub($sa, $object, $params);
        });
    }

    private function doWebmention($syndacct, $object) {
        $details = $this->getAccountDetails($syndacct);
        if (!$details || !isset($details['method'])
            || $details['method'] !== 'webmention') {
            return false;
        }

        // temporarily set the syndication link to the syndication url
        $object->setPosseLink('indiesyndicate', $syndacct);
        $object->save();

        // send a webmention!
        $client = new MentionClient();
        $resp = $client->sendWebmention($object->getSyndicationURL(), $syndacct);

        // bit of a hack to remove the temporary url
        array_pop($object->posse['indiesyndicate']);

        // support for 2xx Created and 3xx Redirect
        if ($resp['code'] >= 200 && $resp['code'] < 400) {
            $msg = "Successfully webmention $syndacct.";
            if (isset($resp['headers']['Location'])) {
                $syndurl = $resp['headers']['Location'];
                $object->setPosseLink(
                    'indiesyndicate', $syndurl, $details['name'], $syndurl, $syndacct,
                    ['icon' => $details['icon']]);
                $msg .= " Returned URL $syndurl.";
            }
            Idno::site()->session()->addMessage($msg);
            Idno::site()->logging()->info($msg);
        } else {
            $msg = "Sending webmention to $syndacct failed.";
            Idno::site()->session()->addErrorMessage($msg);
            Idno::site()->logging()->error($msg, ['response' => $resp]);
        }

        $object->save();
        return true;
    }

    private function doMicropub($syndacct, $object, $params) {
        if ($details = $this->getAccountDetails($syndacct)) {
            $headers = [
                'Authorization: Bearer ' . $details['access_token'],
            ];

            Idno::site()->logging()->debug('sending micropub request', ['endpoint' => $details['micropub_endpoint'], 'params' => $params]);
            $resp = Webservice::post($details['micropub_endpoint'], self::filterEmpty($params), $headers);

            $header = $resp['header'];
            $content = $resp['content'];
            $status = $resp['response'];
            $error = $resp['error'];

            // status should be a 200 or a 300
            if ($status >= 200 && $status < 400) {
                if (preg_match('/Location:(.*)/i', $header, $matches)) {
                    $syndurl = trim($matches[1]);
                    $object->setPosseLink('indiesyndicate', $syndurl, $details['name'], $syndurl, $syndacct, [
                        'icon' => $details['icon'],
                        'style' => $details['style'],
                    ]);
                    $object->save();
                    Idno::site()->session()->addMessage("Syndicated to <a href=\"$syndurl\">$syndurl</a>.");
                } else {
                    $msg = "Received $status from micropub endpoint but no Location header";
                    Idno::site()->logging()->log($msg, LOGLEVEL_ERROR);
                    Idno::site()->session()->addErrorMessage($msg);
                }
            } else {
                if (empty($error) && !empty($content)) {
                    $error = $content;
                }
                $msg = "Error contacting micropub endpoint ($status): $error";
                Idno::site()->logging()->error($msg);
                Idno::site()->session()->addErrorMessage($msg);
            }
        } else {
            $msg = "Could not get account details for syndication account $syndacct";
            Idno::site()->logging()->error($msg);
            Idno::site()->session()->addErrorMessage($msg);
        }
    }

    private function getAccountDetails($syndacct) {
        $user = Idno::site()->session()->currentUser();
        if (isset($user->indiesyndicate[$syndacct])) {
            return $user->indiesyndicate[$syndacct];
        }
        return false;
    }

    private static function filterEmpty($obj) {
        if (is_array($obj)) {
            $res = [];
            foreach ($obj as $k => $v) {
                if (!empty($v)) {
                    $res[$k] = self::filterEmpty($v);
                }
            }
            return $res;
        }
        return $obj;
    }

}