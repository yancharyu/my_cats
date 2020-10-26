<!-- hidden属性があれば -->
<!-- POST送信されたときに画面上部に遷移させないようにする -->
<?php if (!empty($_POST['body_scroll_px'])) : ?>

    <body onload="scrollTo(0, <?php echo $_POST['body_scroll_px']; ?>)">
        <?php unset($_POST['body_scroll_px']); ?>
    <?php elseif (!empty($_SESSION['body_scroll_px'])) : ?>

        <body onload="scrollTo(0, <?php echo $_SESSION['body_scroll_px']; ?>)">
            <?php unset($_SESSION['body_scroll_px']); ?>
        <?php else : ?>

            <body>
            <?php endif; ?>
            <header id="header" class="l-header">
                <div class="p-header">
                    <h1 class="p-header__badge"><a href="index.php" class="p-header__link">MY CATS</a></h1>
                </div>
            </header>