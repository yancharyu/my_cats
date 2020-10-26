<?php
require('function.php');

startProcess('パスワード再発行メール送信ページ');

// POST送信されていたときの処理
if (!empty($_POST)) {
    // POSTで受け取った値を変数に代入
    $email = (!empty($_POST['email'])) ? h($_POST['email']) : '';

    // バリデーション
    debug('バリデーション開始');

    validMaxLen($email, 'email');  // 最大文字数チェック
    validRequired($email, 'email');  // 未入力チェック

    if (empty($err_msg)) {
        debug('バリデーション通過');

        try {
            // DBへ接続
            // POST送信されてきたメールアドレスがデータベースに登録しているか
            $pdo = dbConnect();
            // SQL文作成
            $sql = 'SELECT COUNT(*) as cnt FROM users WHERE email = :email AND delete_flg = :num';
            $data = array(':email' => $email, ':num' => 0);
            // クエリ実行
            $stmt = queryPost($pdo, $sql, $data);
            // クエリ結果の値を取得
            $result = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
            debug('取得した結果', $result);

            if (!empty($result)) {
                debug('メール確認成功。DB登録済み');
                $_SESSION['suc_message'] = "メールを送信しました";

                $auth_key = makeRandKey();  //認証キー生成

                // メールを送信
                $from = 'a@sample.com';
                $to = $email;
                $subject = '【パスワード再発行認証】｜MY_CATS';
                $comment = <<<EOT
                        本メールアドレス宛にパスワード再発行のご依頼がありました。
                        下記の認証キーを入力してください

                        認証キー：{$auth_key}
                        ※認証キーの有効期限は30分となります


                        ////////////////////////////////////////

                        URL  http://localhost:8888/my_webservice/home.php
                        E-mail aieuo@sample.com
                        ////////////////////////////////////////
                        EOT;
                sendMail($from, $to, $subject, $comment);

                //認証に必要な情報をセッションへ保存
                $_SESSION['auth_key'] = $auth_key;
                $_SESSION['auth_email'] = $email;
                $_SESSION['auth_key_limit'] = time() + (60 * 30); //現在時刻より30分後のUNIXタイムスタンプを入れる

                debug('セッション変数の中身', $_SESSION);
                header("Location:passRemindRecieve.php"); //認証キー入力ページへ
                exit();
            } else {
                debug('クエリに失敗したかDBに登録のないEmailが入力されました。');
                $err_msg['common'] = 'メールを送信できませんでした';
            }
        } catch (Exception $e) {
            error_log('エラー発生:' . $e->getMessage());
            $err_msg['common'] = 'メールを送信できませんでした';
        }
    }
}
?>

<?php
$siteTitle = 'パスワード再発行メール送信ページ';
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
                <p class="p-form--margin">ご指定のメールアドレス宛にパスワード再発行用のURLと認証キーをお送り致します。</p>
                <div class="u-errorMessage p-form--margin">
                    <?php if (!empty($err_msg['common'])) echo $err_msg['common']; ?>
                </div>
                <label>
                    メールアドレス<span class="u-require">（必須）</span>
                    <input type="email" name="email" value="<?php echo getFormData('email'); ?>" class="c-input <?php if (!empty($err_msg['pass'])) echo 'u-err'; ?>">
                </label>
                <div class="u-errorMessage p-form--margin">
                    <?php if (!empty($err_msg['email'])) echo $err_msg['email']; ?>
                </div>
                <div class="c-btnContainer">
                    <input type="submit" value="送信" class="c-btn c-btn--flRight">
                </div>
                <a href="login.php" class="p-form__link">ログインページへ</a>
            </form>
        </section>
    </main>
    <?php require('footer.php'); ?>