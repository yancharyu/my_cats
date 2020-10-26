<?php
require('function.php');
startProcess('マイページ');
require('auth.php');

// 現在のページ数を取得
$currentPageNum = (!empty($_GET['page'])) ? (int) $_GET['page'] : 1;

// パラメータがおかしい場合は画面遷移
if (empty($currentPageNum) || !is_int($currentPageNum)) {
    debug('不正な値が入りました');
    debug('トップページに遷移します');
    header('Location:index.php');
    exit();
}

$myData = getUser($_SESSION['user_id']);
$myUploadData = getUploads($myData[0]['id'], $currentPageNum);
?>
<?php
$siteTitle = 'マイページ';
require('head.php');
require('header_hamburger.php');
require('nav-menu_mypage.php');
?>
<main>
    <p class="c-showMsg js-showMsg">
        <?php if (!empty($_SESSION['suc_message'])) echo showSessionMessage('suc_message'); ?>
    </p>
    <section id="main" class="l-siteWidth u-ovHidden">
        <div class="p-profile">
            <div class="p-main p-main--withSidebar">
                <?php
                if (!empty($myData)) :
                    foreach ($myData as $key => $val) :
                        ?>
                        <div class="p-profile__imgContainer">
                            <a href="<?php echo h(showImg($val['back_pic'])); ?>">
                                <img src="<?php echo h(showImg($val['back_pic'])); ?>" class="p-profile__homeImg">
                            </a>
                            <a href="<?php echo h(showImg($val['pic'])); ?>">
                                <img src="<?php echo h(showImg($val['pic'])); ?>" class="p-profile__topImg">
                            </a>
                        </div>
                        <div class="p-profile__prof">
                            <a href="profEdit.php" class="p-profile__profEdit">プロフィールを編集する</a>
                            <p class="p-profile__name"><?php echo h($val['username']); ?></p>
                        </div>
                <?php
                    endforeach;
                endif;
                ?>
                <h1 class="p-profile__title">投稿一覧</h1>
                <div class="p-profile__uploads">
                    <?php
                    if (!empty($myUploadData['data'])) :
                        foreach ($myUploadData['data'] as $key => $val) : ?>
                            <div class="p-panel">
                                <a href="uploadDetail.php?up_id=<?php echo h($val['id']); ?>">
                                    <div class="p-panel__body p-panel__body--padding">
                                        <img src="<?php echo h(showImg($val['pic1'])); ?>" class="p-panel__img">
                                    </div>
                                    <div class="p-panel__footer">
                                        <p class="p-panel__name">お名前： <?php echo h($val['name']); ?></p>
                                        <p class="p-panel__age">年齢： <?php echo h($val['age']); ?>才</p>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p class="u-marginAuto u-textBold">投稿はまだありません</p>
                    <?php endif; ?>
                </div>
                <?php pagination($currentPageNum, $myUploadData['total_page']); ?>
            </div>
        </div>
        <?php require('sidebar_mypage.php'); ?>
    </section>
</main>
<?php require('footer.php'); ?>