<?php

$username = $vars['username'];

$fa = 'paper-plane';
if (stristr($username, 'twitter.com')) {
    $fa = 'twitter';
} else if (stristr($username, 'facebook.com')) {
    $fa = 'facebook';
} else if (stristr($username, 'flickr.com')) {
    $fa = 'flickr';
} else if (stristr($username, 'instagram.com')) {
    $fa = 'instagram';
} else if (stristr($username, 'youtube.com') || stristr($username, 'youtu.be')) {
    $fa = 'youtube';
} else if (stristr($username, 'goodreads.com') || stristr($username, 'librarything.com')) {
    $fa = 'book';
} else if (stristr($username, 'github.com')) {
    $fa = 'github';
} else if (stristr($username, 'git.')) {
    $fa = 'git';
} else if (stristr($username, 'news.')) {
    $fa = 'newspaper';
} else if (stristr($username, 'google.com')) {
    $fa = 'google-plus';
} else if (stristr($username, 'medium.com')) {
    $fa = 'medium';
} else if (stristr($username, 'soundcloud.com')) {
    $fa = 'soundcloud';
} else if (stristr($username, 'pinterest.com')) {
    $fa = 'pinterest';
} else if (stristr($username, 'wordpress.com')) {
    $fa = 'wordpress';
} else if (stristr($username, 'tumblr.com')) {
    $fa = 'tumblr';
}

?>

<i class="fa fa-<?=$fa?>"></i>
