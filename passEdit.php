<?php
require('function.php');
startProcess('パスワード変更ページ');
require('auth.php');

//データベースからパスワードを取得
$userData = getPass($_SESSION['user_id']);
$db_pass = $userData['password'];

//POST送信された時の処理
if (!empty($_POST)) {
    debug('POST送信があります');
    $old_pass = (!empty($_POST['old_pass'])) ? h($_POST['old_pass']) : '';
    $new_pass = (!empty($_POST['new_pass'])) ? h($_POST['new_pass']) : '';
    $pass_re = (!empty($_POST['pass_re'])) ? h($_POST['pass_re']) : '';

    //バリデーション
    // 三項目が入力されているかのバリデーション
    validRequired($old_pass, 'old_pass');
    validRequired($new_pass, 'new_pass');
    validRequired($pass_re, 'pass_re');

    if (empty($err_msg)) {
        // 現在のパスワードが一致しているか
        validMatchPass($old_pass, $db_pass, 'old_pass');

        validPassHalfLen($new_pass, 'new_pass');
        validMaxLen($new_pass, 'new_pass');
    }

    if (empty($err_msg)) {
        // 新しいパスワードと現在のパスワードが異なるか
        validUnMatch($old_pass, $new_pass, 'new_pass', ERR_MSG18);
        validMatch($new_pass, $pass_re, 'pass_re', ERR_MSG07);
    } else {
        debug('バリデーションエラー発生');
        debug('エラー内容', $err_msg);
    }

    if (empty($err_msg)) {
        debug('バリデーション通過');
        debug('パスワードを変更します');

        try {
            $pdo = dbConnect();
            $sql = 'UPDATE users SET password = :pass WHERE id = :id';
            $data = array(
                ':pass' => password_hash($new_pass, PASSWORD_DEFAULT),
                ':id' => $userData['id'],
            );
            $stmt = queryPost($pdo, $sql, $data);
            if (!empty($stmt->rowCount())) {
                debug('パスワードの変更に成功しました');
                $_POST = array();
                $_SESSION['suc_message'] = 'パスワードを変更しました!';
                debug('マイページへ遷移します');
                header('Location:mypage.php');
                exit();
            } else {
                debug('パスワードの変更に失敗しました');
                errMsg('common', ERR_MSG05);
            }
        } catch (Exception $e) {
            debug('エラー発生', $e->getMessage());
            errMsg('common', ERR_MSG05);
        }
    } else {
        debug('バリデーションエラー');
        debug('エラー内容', $err_msg);
    }
}

?>

<?php
$siteTitle = 'パスワード変更';
require('head.php');
?>

<body>
    <?php require('header.php'); ?>
    <main>
        <section id="main" class="l-siteWidth">
            <form action="" method="post" class="p-form">
                <h1 class="p-form__title">パスワード変更</h1>
                <div class="u-errorMessage p-form--margin">
                    <?php if (!empty($err_msg['common'])) echo $err_msg['common']; ?>
                </div>
                <label class=<?php if (!empty($err_msg['old_pass'])) echo 'u-err'; ?>>
                    現在のパスワード<span class="u-require">（必須
                        ）</span>
                    <input type="password" name="old_pass" class="c-input">
                </label>
                <div class="u-errorMessage p-form--margin">
                    <?php if (!empty($err_msg['old_pass'])) echo $err_msg['old_pass']; ?>
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