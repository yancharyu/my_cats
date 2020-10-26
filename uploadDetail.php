<?php
require('function.php');
startProcess('投稿詳細画面');
require('auth.php');

$currentPageNum = (!empty($_GET['page'])) ? $_GET['page'] : 1;
$up_id = (!empty($_GET['up_id'])) ? (int) $_GET['up_id'] : '';
$comment = (!empty($_POST['comment'])) ? $_POST['comment'] : '';
$uploadData = getUploadsOne($up_id);
$messageData = getComments($up_id, $currentPageNum);
//コメント削除ボタンが押された時の処理
if (!empty($_POST['comment-delete'])) {
    debug('コメント削除ボタンが押されました');
    $c_id = (!empty($_POST['del_comment_id'])) ? $_POST['del_comment_id'] : '';
    var_dump($c_id);
    try {
        $pdo = dbConnect();
        $sql = 'UPDATE comments SET delete_flg = :num WHERE id = :c_id';
        $data = array(':num' => 1, ':c_id' => $c_id);

        $stmt = queryPost($pdo, $sql, $data);
        if (!empty($stmt->rowCount())) {
            debug('コメントの削除に成功');
            $_SESSION['body_scroll_px'] = (!empty($_POST['body_scroll_px'])) ? $_POST['body_scroll_px'] : '';
            header('Location:' . $_SERVER['PHP_SELF'] . '?up_id=' . $uploadData[0]['id']);
            exit();
        } else {
            debug('コメントの削除に失敗');
        }
    } catch (Exception $e) {
        debug('コメント削除エラー', $e->getMessage());
    }
}

//削除ボタンが押された時の処理
if (!empty($_POST['delete'])) {
    debug('POST送信があります');
    debug('投稿を削除します');

    try {
        $pdo = dbConnect();
        $sql = 'UPDATE uploads SET delete_flg = :num WHERE id = :up_id';
        $data = array(
            ':num' => 1,
            ':up_id' => $uploadData[0]['id'],
        );

        $stmt = queryPost($pdo, $sql, $data);
        if (!empty($stmt->rowCount())) {
            debug('投稿の削除に成功しました');
            $_SESSION['suc_message'] = '削除しました！';
            header('Location:index.php');
            exit();
        } else {
            debug('投稿の削除に失敗しました');
            errMsg('common', ERR_MSG05);
        }
    } catch (Exception $e) {
        debug('エラーが発生しました', $e->getMessage());
        errMsg('common', ERR_MSG05);
    }
}
//コメント送信ボタンが押された時の処理
//0を送信する可能性もあるのでisset
if (!empty($_POST)) {
    $_SESSION['body_scroll_px'] = (!empty($_POST['body_scroll_px'])) ? $_POST['body_scroll_px'] : '';
    debug('POST送信があります');
    debug('バリデーションチェック開始');
    validMaxLen($comment, 'comment', 100);
    validRequired($comment, 'comment');

    if (empty($err_msg)) {
        debug('バリデーション通過');
        debug('コメントを登録します');
        try {
            $pdo = dbConnect();
            $sql = 'INSERT INTO comments (comments, user_id, board_id, send_date, create_date) VALUES (:comments, :u_id, :b_id, :send_date, :create_date)';
            $data = array(
                ':comments' => $comment,
                ':u_id' => $_SESSION['user_id'],
                ':b_id' => $messageData['board']['id'],
                ':send_date' => date('Y-m-d H:i:s'),
                ':create_date' => date('Y-m-d H:i:s'),
            );

            $stmt = queryPost($pdo, $sql, $data);
            if (!empty($pdo->lastInsertId())) {
                debug('コメントを投稿しました');
                $_POST = array();
                header('Location:' . $_SERVER['PHP_SELF'] . '?up_id=' . $uploadData[0]['id']);
                exit();
            } else {
                debug('コメントの投稿に失敗しました');
            }
        } catch (Exception $e) {
            debug('コメント投稿エラー', $e->getMessage());
        }
    } else {
        debug('バリデーションエラー');
        debug('エラー内容', $err_msg);
    }
}


?>

<?php
$siteTitle = '投稿詳細ページ';
require('head.php');
require('header.php');
?>
<main>
    <section id="main" class="l-siteWidth">
        <div class="p-main">
            <div class="p-uploadDetail">
                <?php if (!empty($uploadData)) :
                    foreach ($uploadData as $key => $val) :
                        ?>
                        <div class="p-uploadDetail__imgContainer">
                            <?php for ($i = 1; $i <= 4; $i++) : ?>
                                <a href="<?php echo h(showImg($val['pic' . $i])); ?>" class="p-uploadDetail__img"><img src="<?php echo h(showImg($val['pic' . $i])); ?>"></a>
                            <?php endfor; ?>
                        </div>

                        <div class="p-uploadDetail__descContainer">
                            <p>お名前： <?php echo h($val['name']); ?></p>
                            <p>年齢： <?php echo h($val['age']); ?>才</p>
                            <p>種類： <?php echo (!empty($val['bre_name'])) ? h($val['bre_name']) : 'その他'; ?></p>
                            <?php if (!empty($val['comment'])) : ?>
                                <p>紹介</p>
                                <div class="p-uploadDetail__descContainer--border">
                                    <?php echo nl2br(h($val['comment'])); ?>
                                </div>
                            <?php else : ?>
                                <p>紹介： 紹介はありません。</p>
                            <?php endif; ?>
                        </div>
                        <div class="p-uploadDetail__commentContainer">
                            <p>コメント</p>
                            <hr color="#000" size="1px">
                            <?php
                                    if (!empty($messageData['comments'])) :
                                        foreach ($messageData['comments'] as $key => $value) :
                                            ?>
                                    <div class="c-avatar">
                                        <a href="profile.php?up_id=<?php echo $up_id; ?>&u_id=<?php echo h($value['user_id']); ?>">
                                            <img src="<?php echo h(showImg($value['pic'])); ?>" class="c-avatar__img c-avatar__img--mini">
                                            <span class="c-avatar__name"><?php echo h($value['username']); ?>さん</span>
                                        </a>
                                        <span>（<?php echo h($value['send_date']); ?>）</span>
                                    </div>
                                    <div class="p-uploadDetail__comment">
                                        <?php echo nl2br(h($value['comments'])); ?>
                                        <?php if ($value['user_id'] === $_SESSION['user_id']) : ?>
                                            <form action="" method="post">
                                                <input type="hidden" name="del_comment_id" value="<?php echo h($value['id']); ?>">
                                                <input type="hidden" name="body_scroll_px" class="js-getBodyScrollPx" value="">
                                                <div class="c-btnContainer">
                                                    <input type="submit" name="comment-delete" value="削除する" class="p-uploadDetail__btn">
                                                </div>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                    <hr color="#000" size="1px">
                                <?php
                                            endforeach;
                                        else :
                                            ?>
                                <p class="p-uploadDetail__comment">コメントはまだありません</p>
                            <?php endif; ?>
                        </div>
                        <?php pagination($currentPageNum, $messageData['total_page'], '&up_id=' . $up_id); ?>
                        <div class="p-uploadDetail__addComment">

                            <form action="" method="post">
                                <input type="hidden" name="body_scroll_px" class="js-getBodyScrollPx" value="">
                                <label class="<?php if (!empty($err_msg['comment'])) echo 'err'; ?>">
                                    コメントする（100文字以内）
                                    <textarea name="comment" cols="10" rows="7" class="c-textBox"><?php getFormData('comment'); ?></textarea>
                                    <div class="u-errorMessage p-form--margin">
                                        <?php if (!empty($err_msg['comment'])) echo $err_msg['comment']; ?>
                                    </div>
                                </label>
                                <div class="c-btnContainer">
                                    <input type="submit" value="投稿" class="c-btn c-btn--mini">
                                </div>
                            </form>
                        </div>
                        <!-- </p-uploadDetail__addComment> -->
                        <!-- 投稿が自分のものだったら -->
                        <div class="p-uploadDetail__uploader">
                            投稿者
                            <?php if ($_SESSION['user_id'] === $val['user_id']) : ?>
                                <div class="c-avatar u-marginTop">
                                    <a href="mypage.php">
                                        <img src="<?php echo h($val['pic']); ?>" class="c-avatar__img">
                                    </a>
                                    <span class="c-avatar__name"><?php echo h($val['username']); ?></span>
                                </div>
                            <?php else : ?>
                                <div class="c-avatar u-marginTop">
                                    <a href="profile.php?u_id=<?php echo h($val['user_id']); ?>">
                                        <img src="<?php echo h($val['pic']); ?>" class="c-avatar__img">
                                    </a>
                                    <span class="c-avatar__name"><?php echo h($val['username']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <!-- </p-uploadDetail__uploader> -->
                        <!-- 投稿が自分の投稿だったら削除ボタンを表示 -->
                        <?php if ($_SESSION['user_id'] === $val['user_id']) : ?>
                            <div class="p-uploadDetail__delete">

                                <form action="" method="post">
                                    <div class="c-btnContainer">
                                        <input type="submit" value="削除する" name="delete" class="c-btn c-btn--mini">
                                    </div>
                                    <div class="c-errorMessage">
                                        <?php if (!empty($err_msg['common'])) echo $err_msg['common']; ?>
                                    </div>
                                </form>
                            </div>
                            <!-- </p-uploadDetail__delete> -->
                        <?php endif; ?>
                <?php
                    endforeach;
                endif;
                ?>
            </div>
            <!-- </p-uploadDetail> -->
        </div>
        <!-- </-main> -->
    </section>
    <!-- </l-siteWidth> -->
</main>
<?php require('footer.php'); ?>