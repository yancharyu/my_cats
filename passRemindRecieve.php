<?php
require('function.php');

startProcess('パスワード再発行メール受信ページ');

//SESSIONに認証キーがあるか確認、なければリダイレクト
if (empty($_SESSION['auth_key'])) {
    header("Location:passRemindSend.php"); //認証キー送信ページへ
}
// POST送信された処理
if (!empty($_POST)) {
    debug('POST送信があります');
    debug('送信された認証キー', $_POST);
    // 認証キーを変数に代入
    $auth_key = $_POST['token'];

    // 未入力チェック
    validRequired($auth_key, 'token');

    if (empty($err_msg)) {
        debug('未入力チェックおけ');
        if ($auth_key !== $_SESSION['auth_key']) {
            $err_msg['common'] = ERR_MSG19;
            debug(ERR_MSG19);
        }
        if (time() > $_SESSION['auth_key_limit']) {
            $err_msg['common'] = ERR_MSG20;
            debug(ERR_MSG20);
        }
        if (empty($err_msg)) {
            debug('認証成功');
            unset($_SESSION['auth_key']);
            unset($_SESSION['auth_key_limit']);
            $_POST = array();
            $_SESSION['suc_auth'] = '1029384756authok';
            header('Location:passCreate.php');
            exit();
        } else {
            debug('認証失敗');
        }
    }
}
?>
<?php
$siteTitle = 'パスワード再発行認証キー入力ページ';
require('head.php');
?>

<body>
    <?php require('header.php'); ?>
    <main>
        <p class="c-showMsg js-showMsg">
            <?php if (!empty($_SESSION['suc_message'])) echo showSessionMessage('suc_message'); ?>
        </p>
        <section id="main" class="l-siteWidth">
            <form action="" method="post" class="p-form">
                <p class="p-form--margin">
                    下記にメールに記載の認証キーを入力してください
                </p>
                <div class="u-errorMessage p-form--margin">
                    <?php if (!empty($err_msg['common'])) echo $err_msg['common']; ?>
                </div>
                <label for="">
                    認証キー
                    <input type="text" name="token" value="<?php echo getFormData('token'); ?>" class="c-input p-form--margin <?php if (!empty($err_msg['token'])) echo 'u-err'; ?>">
                </label>
                <div class="c-btnContainer">
                    <input type="submit" value="送信" class="c-btn c-btn--flRight p-form--margin">
                </div>
                <p>
                    認証キー再発行は<a href="passRemindSend.php" class="p-form__link">コチラ</a>
                </p>
            </form>
        </section>
    </main>
    <?php require('footer.php'); ?>