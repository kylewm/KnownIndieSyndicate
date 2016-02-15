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
                        <label class="col-md-2 control-label">Name</label> This will be displayed as the name of the service on syndication buttons and links.
                        <div class="col-md-10">
                            <input class="form-control" name="name" type="text" value="<?= $details['name'] ?>" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Access Token</label> The micropub access token for this endpoint.
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
