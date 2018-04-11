<?php
// Home
Breadcrumbs::register('home', function ($breadcrumbs) {
    $breadcrumbs->push(trans('gzero-core::common.home'), routeMl('home'));
});

// Home > Account
Breadcrumbs::register('account', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push(trans('gzero-core::user.my_account'), routeMl('account'));
});

// Home > Account > Edit Account
Breadcrumbs::register('account.edit', function ($breadcrumbs) {
    $breadcrumbs->parent('account');
    $breadcrumbs->push(trans('gzero-core::user.edit_account'), routeMl('account.edit'));
});

// Home > Account > OAuth
Breadcrumbs::register('account.oauth', function ($breadcrumbs) {
    $breadcrumbs->parent('account');
    $breadcrumbs->push(trans('gzero-core::user.oauth'), routeMl('account.oauth'));
});

// Error 404
Breadcrumbs::register('errors.404', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push(trans('gzero-core::common.page_not_found'));
});