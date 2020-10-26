<?php
require('function.php');
startProcess('トップページ');
require('auth.php');

//ページング用の値とGETの値を取得する
// 現在のページ数
$currentPageNum = (!empty($_GET['page'])) ? (int) $_GET['page'] : 1;
// 種類のID
$b_id = (!empty($_GET['b_id'])) ? (int) $_GET['b_id'] : '';
// ソート値
$sort = (!empty($_GET['sort'])) ? (int) $_GET['sort'] : 1;
// パラメータがおかしい場合はトップページに遷移させる
if (empty($currentPageNum) || !is_int($currentPageNum)) {
    debug('不正な値が入りました');
    debug('トップページに遷移します');
    header('Location:index.php');
    exit();
}
$uploadData = getUploadLists($currentPageNum, $b_id, $sort);
$breed = getBreed();
?>


<?php
$siteTitle = 'トップページ';
require('head.php');
?>

<body>
    <?php
    require('header_hamburger.php');
    require('nav-menu.php');
    ?>
    <main>
        <!-- セッションを表示する -->
        <p class="c-showMsg js-showMsg">
            <?php echo showSessionMessage('suc_message'); ?>
        </p>
        <section id="main" class="l-siteWidth u-ovHidden">
            <!-- 投稿用のアイコン -->
            <div class="p-topPage">
                <div class="p-main p-main--withSidebar">
                    <div class="p-topPage__uploads">
                        <?php
                        if (!empty($uploadData['data'])) :
                            foreach ($uploadData['data'] as $key => $val) :
                                ?>
                                <div class="p-panel">
                                    <div class="p-panel__head">
                                        <?php if ($val['user_id'] === $_SESSION['user_id']) : ?>
                                            <a href="mypage.php">
                                                <img src="<?php echo h(showImg($val['pic'])); ?>" class="c-avatar__img">
                                            </a>
                                            <span class="c-avatar__name"><?php echo h($val['username']); ?></span>
                                        <?php else : ?>
                                            <a href="profile.php?u_id=<?php echo h($val['user_id']); ?>">
                                                <img src=" <?php echo h(showImg($val['pic'])); ?>" class="c-avatar__img">
                                            </a>
                                            <span class="c-avatar__name"><?php echo h($val['username']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="uploadDetail.php?up_id=<?php echo h($val['id']); ?>">
                                        <div class="p-panel__body u-padding0">
                                            <img src="<?php echo h(showImg($val['pic1'])); ?>" class="p-panel__img">
                                        </div>
                                        <div class="p-panel__footer">
                                            <p>お名前： <?php echo h($val['name']); ?></p>
                                            <p>年齢： <?php echo h($val['age']); ?>才</p>
                                        </div>
                                    </a>
                                </div>
                            <?php
                                endforeach;
                            else :
                                ?>
                            <h1 class="p-topPage__noUploads">まだ投稿がありません</h1>
                        <?php endif; ?>
                    </div>

                    <!-- 第3引数の「'&b_id=' . $b_id . '&sort' . $sort」は、function.phpのpagination関数の引数の第3引数の$linkに渡す値 -->
                    <?php pagination($currentPageNum, $uploadData['total_page'], '&b_id=' . $b_id . '&sort=' . $sort); ?>
                </div>
                <?php require('sidebar.php'); ?>
        </section>
    </main>
    </div>
    <?php
    require('footer.php');
    ?>
</body>