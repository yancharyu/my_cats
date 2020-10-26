<?php
require('function.php');
startProcess('ログアウトページ');
require('auth.php');

debug('ログアウトページ');
debug('セッションを初期化します');
deleteSession();

if (empty($_SESSION)) {
    debug('セッションの初期化に成功');
    debug('ホームページへ遷移');
    header('Location:home.php');
    exit();
} else {
    debug('セッションの初期化に失敗しました');
}
