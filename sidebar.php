<section id="sidebar" class="l-sidebar">
    <div class="p-sidebar js-sidebarMenu">

        <!-- リファクタ -->
        <!-- style属性付加× -->
        <a href="upload.php" class="p-sidebar__item">投稿する</a>
        <a href="mypage.php" class="p-sidebar__item">マイページへ</a>
        <div class="p-sort">

            <p class="p-sort__title">絞り込み検索</p>
            <form action="" method="get">
                <p class="p-sort__menu">種類</p>
                <select name="b_id" class="c-selectBox c-selectBox--widthMax">
                    <option value="" <?php if (getFormData('b_id', true) == '') echo 'selected'; ?>>全て</option>
                    <?php if (!empty($breed)) :
                        foreach ($breed as $key => $val) : ?>
                            <option value="<?php echo $val['id']; ?>" <?php if (getFormData('b_id', true) == $val['id']) echo 'selected'; ?>><?php echo $val['name']; ?></option>
                    <?php endforeach;
                    endif;
                    ?>
                </select>
                <p class="p-sort__menu">投稿日時</p>
                <select name="sort" class="c-selectBox c-selectBox--widthMax">
                    <option value="1" <?php if (getFormData('sort', true) == 1) echo 'selected'; ?>>投稿が新しい順</option>
                    <option value="2" <?php if (getFormData('sort', true) == 2) echo 'selected'; ?>>投稿が古い順</option>
                </select>
                <div class="c-btnContainer">
                    <input type="submit" value="検索する" class="p-sort__btn">
                </div>
            </form>
        </div>
    </div>
</section>