<?php
require('function.php');
startProcess('プロフィール画面');
require('auth.php');

$currentPageNum = (!empty($_GET['page'])) ? (int) $_GET['page'] : 1;
$u_id = (!empty($_GET['u_id'])) ? $_GET['u_id'] : '';
$userData = getUser($u_id);
$uploadData = getUploads($userData[0]['id'], $currentPageNum);
?>

<?php
$siteTitle = 'プロフィール画面';
require('head.php');
?>

<body>
    <?php require('header.php'); ?>
    <section id="main" class="l-siteWidth u-ovHidden">
        <div class="p-main">
            <div class="p-profile">
                <?php if (!empty($userData)) :
                    foreach ($userData as $key => $val) :
                        ?>
                        <div class="p-profile__imgContainer">

                            <a href="<?php echo h(showImg($val['back_pic'])); ?>">
                                <img src="<?php echo h(showImg($val['back_pic'])); ?>" class="p-profile__homeImg">
                            </a>
                            <a href="<?php echo h(showImg($val['pic'])); ?>">
                                <img src="<?php echo h(showImg($val['pic'])); ?>" class="p-profile__topImg">
                            </a>
                        </div>
                        <!-- </p-profile__imgContainer> -->
                        <div class="p-profile__prof">
                            <p class="p-profile__name"><?php echo h($val['username']); ?></p>
                        </div>
                <?php endforeach;
                endif;
                ?>
                <h1 class="p-profile__title">投稿一覧</h1>
                <div class="p-profile__uploads">

                    <?php if (!empty($uploadData)) :
                        foreach ($uploadData['data'] as $key => $val) : ?>
                            <div class="p-panel">
                                <a href="uploadDetail.php?up_id=<?php echo h($val['id']); ?>">
                                    <div class="p-panel__body p-panel__body--padding">
                                        <img src="<?php echo h(showImg($val['pic1'])); ?>" class="p-panel__img">
                                    </div>
                                    <!-- </p-panel__body> -->
                                    <div class="p-panel__footer">
                                        <p class="p-panel__name">お名前： <?php echo h($val['name']); ?></p>
                                        <p class="p-panel__age">年齢： <?php echo h($val['age']); ?>才</p>
                                    </div>
                                    <!-- </p-panel__footer> -->
                                </a>
                            </div>
                            <!-- </p-panel> -->
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p>投稿はまだありません</p>
                    <?php
                    endif;
                    ?>
                </div>
                <?php pagination($currentPageNum, $uploadData['total_page'], '&u_id=' . $u_id);
                ?>
                <!-- </p-profile__uploads> -->
            </div>
            <!-- </p-profile> -->

    </section>
    </div>
    <?php require('footer.php'); ?>