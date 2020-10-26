<?php
require('function.php');

startProcess('パスワード再発行ページ');

$email = (!empty($_SESSION['auth_email'])) ? $_SESSION['auth_email'] : '';

if (empty($_SESSION['suc_auth']) || empty($email) || $_SESSION['suc_auth'] !== '1029384756authok') {
    debug('不正なアクセス');
    header('Location:home.php');
}

//POST送信された時の処理
if (!empty($_POST)) {
    debug('POST送信があります');
    $new_pass = (!empty($_POST['new_pass'])) ? h($_POST['new_pass']) : '';
    $pass_re = (!empty($_POST['pass_re'])) ? h($_POST['pass_re']) : '';


    //バリデーション
    validPassHalfLen($new_pass, 'new_pass');
    validMaxLen($new_pass, 'new_pass');
    validRequired($new_pass, 'new_pass');
    validRequired($pass_re, 'pass_re');
    if (empty($err_msg)) {
        validMatch($new_pass, $pass_re, 'pass_re', ERR_MSG07);
    }
    if (empty($err_msg)) {
        debug('バリデーション通過');
        debug('新しいパスワードを登録します');

        try {
            $pdo = dbConnect();
            $sql = 'UPDATE users SET password = :pass WHERE email = :email';
            $data = array(
                ':pass' => password_hash($new_pass, PASSWORD_DEFAULT),
                ':email' => $email
            );

            $stmt = queryPost($pdo, $sql, $data);
            if (!empty($stmt->rowCount())) {
                debug('パスワードの変更に成功');
                debug('セッションを削除します');
                $_SESSION = array();
                $_POST = array();
                $_SESSION['suc_message'] = 'パスワードを変更しました!';
                debug('背ーーーsション変数の中身', $_SESSION);
                header('Location:login.php');
                exit();
            } else {
                debug('パスワードの変更に失敗しました');
                $err_msg['common'] = ERR_MSG05;
            }
        } catch (Exception $e) {
            debug('エラー発生', $e->getMessage());
            errMsg('common', ERR_MSG05);
        }
    } else {
        debug('バリデーションエラー');
    }
    $_POST = array();
}
?>

<?php
$siteTitle = 'パスワード再発行';
require('head.php');
?>

<body>
    <?php require('header.php'); ?>
    <main>
        <section id="main" class="l-siteWidth">
            <form action="" method="post" class="p-form">
                <h1 class="p-form__title">パスワード再発行</h1>
                <div class="u-errorMessage p-form--margin">
                    <?php if (!empty($err_msg['common'])) echo $err_msg['common']; ?>
                </div>
                <label>
                    新しいパスワード<span class="u-require">（必須）</span>
                    <input type="password" name="new_pass" class="c-input <?php if (!empty($err_msg['new_pass'])) echo 'u-err'; ?>">
                </label>
                <div class="u-errorMessage p-form--margin">
                    <?php if (!empty($err_msg['new_pass'])) echo $err_msg['new_pass']; ?>
                </div>
                <label>
                    パスワード再入力<span class="u-require">（必須
                        ）</span>
                    <input type="password" name="pass_re" class="c-input <?php if (!empty($err_msg['pass_re'])) echo 'u-err'; ?>">
                </label>
                <div class="u-errorMessage p-form--margin">
                    <?php if (!empty($err_msg['pass_re'])) echo $err_msg['pass_re']; ?>
                </div>
                <div class="c-btnContainer">
                    <input type="submit" value="変更する" class="c-btn c-btn--flRight">
                </div>
            </form>
        </section>
    </main>
    <?php require('footer.php'); ?>