<?php

//===================
// ログイン認証（自動ログアウト）
//===================

debug('auth.phpのログイン認証を開始します');
// ユーザー情報を取得


//ログインしている場合
if (!empty($_SESSION['login_date'])) {
    debug('ログイン済みユーザーです');

    //セッション有効期限が切れていた場合
    if (($_SESSION['login_date'] + $_SESSION['login_limit']) < time()) {
        debug('ログイン有効期限が切れています');
        //セッションを削除して（ログアウトさせる）してログインページへ遷移する
        debug('セッションを削除してホームページに遷移します');
        deleteSession();
        header('Location:home.php');
        exit();
        //ログイン有効期限内だった場合
    } else {
        debug('ログイン有効期限内です');
        //ログイン日時を現在のUNIXタイムスタンプに更新
        $_SESSION['login_date'] = time();
        debug('ログイン認証完了');

        //現在実行中のスクリプトファイル名がlogin.php, home.php, signup.phpのいずれかの場合はマイページに遷移
        if (basename($_SERVER['PHP_SELF']) === 'login.php' || basename($_SERVER['PHP_SELF']) === 'home.php' || basename($_SERVER['PHP_SELF']) === 'signup.php') {
            debug('マイページへ遷移します');
            header('Location:mypage.php');
            exit();
        }
    }
    // 未ログインユーザーの場合
} else {
    debug('未ログインユーザーです');
    //現在実行中のスクリプトファイル名がlogin.php, home.php, signup.phpのいずれかの場合はホームページへの遷移は行わない
    if (basename($_SERVER['PHP_SELF']) === 'home.php' || basename($_SERVER['PHP_SELF']) === 'login.php' || basename($_SERVER['PHP_SELF']) === 'signup.php') {
        return;
    }
    debug('ホームページへ遷移します');
    header('Location:home.php');
    exit();
}
