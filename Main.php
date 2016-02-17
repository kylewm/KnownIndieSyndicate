<?php

namespace IdnoPlugins\IndieSyndicate;

use Idno\Core\Idno;
use Idno\Core\Event;
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

        Idno::site()->template()->extendTemplate('account/menu/items', 'account/menu/items/indiesyndicate');
    }

    function registerEventHooks()
    {
        parent::registerEventHooks();

        Idno::site()->syndication()->registerService('indiesyndicate', function () {
            return true;
        }, ['note', 'article', 'image', 'like', 'share']);


        Idno::site()->addEventHook('user/auth/success', function (Event $event) {
            $is = Idno::site()->session()->currentUser()->indiesyndicate;
            foreach ($is as $url => $details) {
                Idno::site()->syndication()->registerServiceAccount('indiesyndicate', $url, $details['name']);
            }
        });

        Idno::site()->addEventHook('post/note/indiesyndicate', function (Event $event) {
            $eventdata = $event->data();
            $sa = $eventdata['syndication_account'];
            $object = $eventdata['object'];
            $params = [
                'h' => 'entry',
                'content' => $object->body,
                'url' => $object->getSyndicationURL(),
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

            $params = [
                'h' => 'entry',
                'name' => $object->title,
                'content' => $object->body,
                'url' => $object->getSyndicationURL(),
            ];

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
            $params = [
                'h' => 'entry',
                'name' => $object->title,
                'content' => $object->body,
                'url' => $object->getSyndicationURL(),
            ];
            $this->doMicropub($sa, $object, $params);
        });

        Idno::site()->addEventHook('post/like/indiesyndicate', function (Event $event) {
            $eventdata = $event->data();
            $sa = $eventdata['syndication_account'];
            $object = $eventdata['object'];
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
            $params = [
                'h' => 'entry',
                'repost-of' => $object->repostof,
                'url' => $object->getSyndicationURL(),
            ];
            $this->doMicropub($sa, $object, $params);
        });
    }

    private function doMicropub($syndacct, $object, $params) {
        $user = Idno::site()->session()->currentUser();
        if (!empty($user->indiesyndicate[$syndacct])) {
            $details = $user->indiesyndicate[$syndacct];
            $headers = [
                'Authorization: Bearer ' . $details['access_token'],
            ];

            $resp = Webservice::post($details['micropub_endpoint'], self::filterEmpty($params), $headers);

            $header = $resp['header'];
            $content = $resp['content'];
            $status = $resp['response'];
            $error = $resp['error'];

            // status should be a 200 or a 300
            if ($status >= 200 && $status < 400) {
                if (preg_match('/Location:(.*)/i', $header, $matches)) {
                    $syndurl = trim($matches[1]);
                    $object->setPosseLink('indiesyndicate', $syndurl, $details['name'], $syndurl, $syndacct);
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
                Idno::site()->logging()->log($msg, LOGLEVEL_ERROR);
                Idno::site()->session()->addErrorMessage($msg);
            }
        }
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