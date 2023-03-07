<?php

switch(@parse_url($_SERVER['REQUEST_URI'])['path']){
    case '/404.php':
        require '404.php';
    break;
    case '/403.php':
        require '403.php';
    break;
    case '/account-history.php':
        require 'account-history.php';
    break;
    case '/activity-history.php':
        require 'activity-history.php';
    break;
    case '/broadcast.php':
        require 'broadcast.php';
    break;
    case '/category.php':
        require 'category.php';
    break;
    case '/comment-control.php':
        require 'comment-control.php';
    break;
    case '/comment-list.php':
        require 'comment-list.php';
    break;
    case '/controls.php':
        require 'controls.php';
    break;
    case '/create.php':
        require 'create.php';
    break;
    case '/dashboard.php':
        require 'dashboard.php';
    break;
    case '/edit-post.php':
        require 'edit-post.php';
    break;
    case '/faq.php':
        require 'faq.php';
    break;
    case '/forgot-password.php':
        require 'forgot-password.php';
    break;
    case '/forum.php':
        require 'forum.php';
    break;
    case '/generate-key.php':
        require 'generate-key.php';
    break;
    case '/how-we-work.php':
        require 'how-we-work.php';
    break;
    case '/':
        require 'index.php';
    break;
    case '/index.php':
        require 'index.php';
    break;
    case '/learn-more.php':
        require 'learn-more.php';
    break;
    case '/post-control.php':
        require 'post-control.php';
    break;
    case '/post-list.php':
        require 'post-list.php';
    break;
    case '/remove-account.php':
        require 'remove-account.php';
    break;
    case '/replies.php':
        require 'replies.php';
    break;
    case '/reset-password.php':
        require 'reset-password.php';
    break;
    case '/settings.php':
        require 'settings.php';
    break;
    case '/signin.php':
        require 'signin.php';
    break;
    case '/signout.php':
        require 'signout.php';
    break;
    case '/signup.php':
        require 'signup.php';
    break;
    case '/site-stats.php':
        require 'site-stats.php';
    break;
    case '/subscribe.php':
        require 'subscribe.php';
    break;
    case '/subscription-history.php':
        require 'subscription-history.php';
    break;
    case '/terms.php':
        require 'terms.php';
    break;
    case '/user-control.php':
        require 'user-control.php';
    break;
    case '/user-list.php':
        require 'user-list.php';
    break;
    case '/user.php':
        require 'user.php';
    break;
    // case '/verify.php':
    //     require 'verify.php';
    // break;
    case '/withdraw.php':
        require 'withdraw.php';
    break;
    case '/withdrawal-control.php':
        require 'withdrawal-control.php';
    break;
    case '/withdrawal-list.php':
        require 'withdrawal-list.php';
    break;
    case '/includes/fetch-account-history.php':
        require 'includes/fetch-account-history.php';
    break;
    case '/includes/fetch-activity-history.php':
        require 'includes/fetch-activity-history.php';
    break;
    case '/includes/fetch-category.php':
        require 'includes/fetch-category.php';
    break;
    case '/includes/fetch-comment-list.php':
        require 'includes/fetch-comment-list.php';
    break;
    case '/includes/fetch-forum-comments.php':
        require 'includes/fetch-forum-comments.php';
    break;
    case '/includes/fetch-index.php':
        require 'includes/fetch-index.php';
    break;
    case '/includes/fetch-post-list.php':
        require 'includes/fetch-post-list.php';
    break;
    case '/includes/fetch-replies.php':
        require 'includes/fetch-replies.php';
    break;
    case '/includes/fetch-subscription-history.php':
        require 'includes/fetch-subscription-history.php';
    break;
    case '/includes/fetch-user-control-account.php':
        require 'includes/fetch-user-control-account.php';
    break;
    case '/includes/fetch-user-list.php':
        require 'includes/fetch-user-list.php';
    break;
    case '/includes/fetch-user-posts.php':
        require 'includes/fetch-user-posts.php';
    break;
    case '/includes/fetch-withdrawal-list.php':
        require 'includes/fetch-withdrawal-list.php';
    break;
    default:
        require '404.php';
    break;
}
