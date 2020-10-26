<?php
require('function.php');
require('auth.php');
startProcess('新規登録ページ');

//POST送信されていた時
if (!empty($_POST)) {
    $email = (!empty($_POST['email'])) ? h($_POST['email']) : '';
    $pass = (!empty($_POST['password'])) ? h($_POST['password']) : '';
    $pass_re = (!empty($_POST['pass_re'])) ? h($_POST['pass_re']) : '';

    //バリデーション開始
    debug('バリデーションを開始します');

    //emailバリデーション
    validEmailDup($email, 'email');  //重複チェック
    validEmail($email, 'email');  //形式チェック
    validMaxLen($email, 'email');  //最大文字数チェック
    validRequired($email, 'email');  //未入力チェック

    //パスワードバリデーション
    validPassHalfLen($pass, 'pass');  //半角英数字文字数チェック
    validMaxLen($pass, 'pass');  //最大文字数チェック
    validRequired($pass, 'pass');  //未入力チェック
    validRequired($pass_re, 'pass_re');  //未入力チェック

    if (empty($err_msg)) {
        // パスワード再入力バリデーション
        validMatch($pass, $pass_re, 'pass_re', '再入力用と一致しません');  //同値チェック

        if (empty($err_msg)) {
            debug('バリデーション通過');
            debug('データベースに登録します');

            //例外処理
            try {
                $pdo = dbConnect();
                $sql = 'INSERT INTO users (email, password, login_date, create_date) VALUES (:email, :password, :login_date, :create_date)';
                $data = array(
                    ':email' => $email,
                    ':password' => password_hash($pass, PASSWORD_DEFAULT),
                    ':login_date' => date('Y-m-d H:i:s'),
                    ':create_date' => date('Y-m-d H:i:s'),
                );

                $stmt = queryPost($pdo, $sql, $data);

                //クエリ成功の場合
                if (!empty($stmt)) {
                    // セッションの有効期限をデフォルトで１時間とする
                    $sesLimit = 60 * 60;

                    $_SESSION['user_id'] = $pdo->lastInsertId();
                    $_SESSION['login_date'] = time();
                    $_SESSION['login_limit'] = $sesLimit;
                    debug('$SESSION変数の中身', $_SESSION);
                    //マイページへ遷移
                    $_POST = array();
                    debug('トップページへ遷移');
                    header('Location:index.php');
                    exit();
                }
            } catch (Exception $e) {
                debug('エラーが発生しました', $e->getMessage());
                errMsg('common', ERR_MSG05);
            }
        }
    } else {
        debug('バリデーションエラー発生', $err_msg);
    }
}
?>
<?php
$siteTitle = '新規登録';
require('head.php');
?>

<body>
    <?php require('header.php'); ?>
    <main>
        <section id="main" class="l-siteWidth">
            <form action="" method="post" class="p-form">
                <h1 class="p-form__title">新規登録</h1>
                <div class="u-errorMessage p-form--margin">
                    <?php if (!empty($err_msg['common'])) echo $err_msg['common']; ?>
                </div>
                <label>
                    メールアドレス<span class="u-require">（必須）</span>
                    <input type="email" name="email" value="<?php echo h(getFormData('email')); ?>" class="c-input <?php if (!empty($err_msg['email'])) echo 'u-err'; ?>">
                </label>
                <div class="u-errorMessage p-form--margin">
                    <?php if (!empty($err_msg['email'])) echo $err_msg['email']; ?>
                </div>
                <label>
                    パスワード<span class="u-require">（必須）</span>
                    <input type="password" name="password" class="c-input <?php if (!empty($err_msg['email'])) echo 'u-err'; ?>">
                </label>
                <div class="u-errorMessage p-form--margin">
                    <?php if (!empty($err_msg['pass'])) echo $err_msg['pass']; ?>
                </div>
                <label>
                    パスワード再入力<span class="u-require">（必須）</span>
                    <input type="password" name="pass_re" class="c-input <?php if (!empty($err_msg['pass_re'])) echo 'u-err'; ?>">
                </label>
                <div class="u-errorMessage p-form--margin">
                    <?php if (!empty($err_msg['pass_re'])) echo $err_msg['pass_re']; ?>
                </div>
                <div class="c-btnContainer">
                    <input type="submit" value="登録する" class="c-btn c-btn--flRight">
                </div>
                <p class="p-form--margin">
                    ログインは<a href="login.php" class="p-form__link">コチラ</a>
                </p>
            </form>
        </section>
    </main>
    <?php require('footer.php'); ?>