<?php
require('function.php');
require('auth.php');

startProcess('ログインページ');

debug('セッション変数の中身', $_SESSION);
//POST送信された時の処理
if (!empty($_POST)) {
    // POSTで受け取った値を変数に代入
    $email = (!empty($_POST['email'])) ? h($_POST['email']) : '';
    $pass = (!empty($_POST['password'])) ? h($_POST['password']) : '';

    //バリデーション
    debug('バリデーション開始');
    // メールアドレスバリデーションチェック

    validMaxLen($email, 'email');  // 最大文字数チェック
    validRequired($email, 'email');  // 未入力チェック
    validPassHalfLen($pass, 'pass');
    validRequired($pass, 'pass');  //未入力チェック

    if (empty($err_msg)) {
        debug('バリデーション通過');

        try {
            $pdo = dbConnect();
            $sql = 'SELECT id, username, password, login_date FROM users WHERE email = :email AND delete_flg = 0';
            $data = array(':email' => $email);
            $stmt = queryPost($pdo, $sql, $data);
            $rst = $stmt->fetch();

            //パスワードがデータベースのパスワードと一致した時
            if (!empty($rst) && password_verify($_POST['password'], $rst['password'])) {
                debug('パスワードが一致しました');
                debug('ログインに成功しました');
                //セッションの有効期限をデフォルトで１時間に設定する
                $sesLimit = 60 * 60;
                $_SESSION['login_date'] = time();

                //ログイン短縮にチェックがある場合
                //変数に入れてそれを比較してもいいが、もしどこかで変数の中身が変わってしまうと処理が変わってしまうのでPOSTで受け取った値をそのまま代入する
                if (!empty($_POST['login_save'])) {
                    debug('ログイン短縮にチェックがあります');
                    //セッションの有効期限を30日に設定する
                    debug('セッションの有効期限を30日に設定します');

                    $sesLimit *= 24 * 30;
                    $_SESSION['login_limit'] = $sesLimit;
                    debug('セッション有効期限の値', $_SESSION['login_limit']);
                } else {
                    debug('ログイン短縮にチェックはありません');
                    $_SESSION['login_limit'] = $sesLimit;
                    debug('セッションの有効期限', $_SESSION['login_limit']);
                }

                //データベースから取得したIDをセッションIDに格納
                $_SESSION['user_id'] = $rst['id'];

                //データベースの最終ログイン日時を更新する
                if (!empty($_SESSION['user_id'])) {
                    try {
                        $sql = 'UPDATE users SET login_date = :date WHERE id = :id';
                        $data = array(
                            ':date' => date('Y-m-d H:i:s'),
                            ':id' => $_SESSION['user_id'],
                        );

                        $stmt = queryPost($pdo, $sql, $data);
                        $cnt = $stmt->rowCount();

                        if (!empty($cnt)) {
                            debug('最終ログイン日時の更新に成功しました');
                        } else {
                            debug('最終ログイン日時の更新に失敗しました');
                        }
                    } catch (Exception $e) {
                        debug('login_date更新エラー', $e->getMessage());
                    }
                }

                $_SESSION['suc_message'] = "ようこそ！ {$rst['username']}さん！";
                //現在のセッションIDを新しく生成したものと置き換える（なりすましのセキュリティ対策）
                debug('session_regenerate_id関数を実行します');
                $_POST = array();
                session_regenerate_id();
                debug('トップページへ遷移');
                header('Location:index.php');
                exit();
                //パスワードが一致しなかった場合
            } else {
                debug('パスワードが一致しませんでした');
                errMsg('common', ERR_MSG10);
            }
        } catch (Exception $e) {
            debug('エラー発生', $e->getMessage());
            errMsg('common', ERR_MSG05);
        }
        // バリデーションエラーがあった場合
    } else {
        debug('バリデーションエラー発生');
        debug('エラー内容', $err_msg);
    }
}
?>
<?php
$siteTitle = 'ログインページ';
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
                <p class="p-form__title u-textBold">ログイン</p>
                <div class="u-errorMessage p-form--margin">
                    <?php if (!empty($err_msg['common'])) echo $err_msg['common']; ?>
                </div>
                <label>
                    メールアドレス<span class="u-require">（必須）</span>
                    <input type="email" name="email" value="<?php echo getFormData('email'); ?>" class="c-input <?php if (!empty($err_msg['email'])) echo 'u-err'; ?>">
                </label>
                <div class="u-errorMessage p-form--margin">
                    <?php if (!empty($err_msg['email'])) echo $err_msg['email']; ?>
                </div>
                <label>
                    パスワード<span class="u-require">
                        （必須）</span>
                    <input type="password" name="password" class="c-input <?php if (!empty($err_msg['pass'])) echo 'u-err'; ?>">
                </label>
                <div class="u-errorMessage p-form--margin">
                    <?php if (!empty($err_msg['pass'])) echo $err_msg['pass']; ?>
                </div>
                <label>
                    <input type="checkbox" name="login_save" id="login_save">
                    次回から自動でログイン
                </label>
                <div class="c-btnContainer p-form--margin">
                    <input type="submit" value="ログイン" class="c-btn c-btn--flRight">
                </div>
                <p class="p-form--margin">
                    <a href="passRemindSend.php" class="p-form__link">パスワードをお忘れですか？</a>
                </p>
                <p>
                    初めての方は<a href="signup.php" class="p-form__link">コチラ</a>
                </p>
            </form>
        </section>
    </main>
    <?php require('footer.php'); ?>