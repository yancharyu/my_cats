<?php
$siteTitle = 'MY CATS';
require('function.php');
require('auth.php');
require('head.php');
?>

<div class="l-hero p-hero" id="hero">
    <div class="p-hero__badge">
        <h1><a href="index.php" class="p-hero__mainLink p-hero__letter">MY CATS</a></h1>
    </div>
    <!-- /hero__title> -->
    <div class="p-hero__text p-hero__letter">
        <p>おうちで大切に飼われているかわいい猫ちゃんたち。</p>
        <p>ご自身のネコや他の方が飼っているネコの写真を共有してみませんか？</p>
    </div>
    <div class="p-hero__linkContainer">
        <span><a href="login.php" class="p-hero__link p-hero__letter">ログイン </a></span> | <span><a href="signup.php" class="p-hero__link p-hero__letter"> 新規登録</a></span>
    </div>
    <!-- /home__content> -->
</div>
</body>

</html>