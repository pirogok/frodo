<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
| Как принято в REST, запросы можно реализовать cURL или JS
|
| $app->get('/accounts', 'AccountsController@listAccounts');
| $app->post('/accounts', 'AccountsController@createAccount');
| $app->put('/accounts/{account_id}', 'AccountsController@editAccount');
| $app->delete('/accounts/{account_id}', 'AccountsController@deleteAccount');
| $app->get('/posts/{account_id}/{limit?}', 'AccountsController@listPosts');
|
| при удалении если удалять постом lumen ругается что запрос поститься, а данные постом не передаются. сделал гет
| В получение твитов надо как то передать limit. 
| Тесть сделать постом и передавать параметр в теле, или добавить ище один гет параметр
| Сам принимать такие решения не могу, надо согласовывать с клиетом
| ->were() В люмене не работает, надо разбиратся с проверкой
|
*/
$app->get('/accounts', 'AccountsController@listAccounts');
$app->post('/accounts/new', 'AccountsController@createAccount');
$app->post('/accounts/{account_id}', 'AccountsController@editAccount');//->where('account_id', '[0-9]+');
$app->get('/accounts/{account_id}/delete', 'AccountsController@deleteAccount');//->where('account_id', '[0-9]+');
$app->get('/accounts/posts/{account_id}', 'AccountsController@listPosts');//->where('account_id', '[0-9]+');
$app->get('/accounts/gettwitterposts', 'AccountsController@getTwitterPosts');