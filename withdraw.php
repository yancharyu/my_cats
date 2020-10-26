<?php
require('function.php');
startProcess('退会ページ');
require('auth.php');


if (!empty($_POST)) {
    debug('POST送信があります');
    debug('退会処理を実行します');

    try {
        $pdo = dbConnect();
        $sql1 = 'UPDATE users SET delete_flg = :num WHERE id = :id';
        $sql2 = 'UPDATE uploads SET delete_flg = :num WHERE user_id = :id';
        $sql3 = 'UPDATE favos SET delete_flg = :num WHERE user_id = :id';
        $data = array(
            ':num' => 1,
            ':id' => $_SESSION['user_id'],
        );
        $stmt1 = queryPost($pdo, $sql1, $data);
        $stmt3 = queryPost($pdo, $sql2, $data);
        $stmt2 = queryPost($pdo, $sql3, $data);

        if (!empty($stmt1->rowCount())) {
            debug('退会に成功しました');
            debug('セッションを削除してホームページに遷移します');
            $_POST = array();
            deleteSession();
            debug('セッションの中身', $_SESSION);
            header('Location:home.php');
            exit();
        } else {
            debug('退会処理に失敗しました');
            errMsg('common', ERR_MSG05);
        }
    } catch (Exception $e) {
        debug('エラー発生', $e->getMessage());
        errMsg('common', ERR_MSG05);
    }
}
?>

<?php
$siteTitle = '退会ページ';
require('head.php');
?>

<body>
    <?php require('header.php'); ?>
    <main>
        <section id="main" class="l-siteWidth">
            <form action="" method="post" class="p-form">
                <p class="p-form__title p-form--margin">本当に退会しますか？</p>
                <div class="u-errorMessage">
                    <?php if (!empty($err_msg['common'])) echo $err_msg['common']; ?>
                </div>
                <div class="c-btnContainer p-form--margin">
                    <input type="submit" name="submit" value="退会する" class="c-btn c-btn--flRight">
                </div>
                <a href="mypage.php" class="p-form__link">マイページへ</a>
            </form>
        </section>
    </main>
    <?php require('footer.php'); ?>