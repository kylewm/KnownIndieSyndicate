<?php

use Idno\Core\Idno;

$baseURL = Idno::site()->config()->getDisplayURL();
$user = Idno::site()->session()->currentUser();
?>

<div class="col-md-offset-1 col-md-10">

    <?= $this->draw('account/menu') ?>

    <h1>IndieSyndicate Accounts</h1>

    <?php foreach ((array) $user->indiesyndicate as $url => $details) { ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                Target: <?= $url ?>
            </div>
            <div class="panel-body">
                <form class="form-horizontal" action="<?= $baseURL ?>account/indiesyndicate/edit" method="POST">
                    <input type="hidden" name="url" value="<?= $url ?>" />
                    <div class="form-group">
                        <label class="col-md-2 control-label">Name</label>
                        <div class="col-md-10">
                            <input class="form-control" name="name" type="text" value="<?= $details['name'] ?>" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Icon</label>
                        <div class="col-md-10">
                            <select class="form-control" name="icon" style="font-family: FontAwesome, sans;">
                                <?php foreach ([
                                    "paper-plane" => "&#xf1d8;",
                                    "twitter"     => "&#xf099;",
                                    "facebook"    => "&#xf09a;",
                                    "flickr"      => "&#xf16e;",
                                    "instagram"   => "&#xf16d;",
                                    "youtube"     => "&#xf167;",
                                    "book"        => "&#xf02d;",
                                    "github"      => "&#xf09b",
                                    "git"         => "&#xf1d3",
                                    "newspaper-o" => "&#xf1ea",
                                    "google-plus" => "&#xf0d5",
                                    "medium"      => "&#xf23a;",
                                    "soundcloud"  => "&#xf1be;",
                                    "pinterest"   => "&#xf0d2;",
                                    "wordpress"   => "&#xf19a;",
                                    "tumblr"      => "&#xf173;",
                                ] as $option => $unicode) {?>
                                    <option value="<?= $option ?>" <?= isset($details['icon']) && $details['icon'] === $option ? ' selected' : '' ?>>
                                        <?= $unicode ?> <?=$option?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Style</label>
                        <div class="col-md-10">
                            <select class="form-control" name="style">
                                <?php foreach ([
                                    "default" => "Default",
                                    "microblog" => "Microblog",
                                    "social" => "Social Media",
                                    "photos" => "Photo Sharing",
                                ] as $option => $display) { ?>
                                    <option value="<?= $option ?>" <?= isset($details['style']) && $details['style'] === $option ? ' selected' : '' ?>>
                                        <?= $display ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Access Token</label>
                        <div class="col-md-10">
                            <input class="form-control" type="text" value="<?= $details['access_token'] ?>" disabled />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Endpoint</label>
                        <div class="col-md-10">
                            <input class="form-control" type="text" value="<?= $details['micropub_endpoint'] ?>" disabled />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-offset-2 col-md-10">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                    <?= Idno::site()->actions()->signForm('/account/indiesyndicate/') ?>
                </form>
            </div>
        </div>
    <?php } ?>

    <div class="panel panel-primary">
        <div class="panel-heading">
            Add a New Syndication Target
        </div>
        <div class="panel-body">
            <form class="form-horizontal" action="<?= $baseURL ?>account/indiesyndicate/add" method="POST">
                <div class="form-group">
                    <label class="col-md-2">Target URL</label>
                    <div class="col-md-10">
                        <input class="form-control" name="url" type="url" placeholder="http://..." />
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-offset-2 col-md-10">
                        <button class="btn btn-primary" type="submit">Add</button>
                        <?= Idno::site()->actions()->signForm('/account/indiesyndicate/') ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>
