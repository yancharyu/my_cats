<?php
require('function.php');
startProcess('プロフィール編集ページ');
require('auth.php');

// DBからユーザー情報を取得
$dbUploadData = getUser($_SESSION['user_id'])[0];


// POST送信されていた場合
if (!empty($_POST)) {
    debug('POST送信があります');
    $username = ($_POST['username'] === '') ? '名無し' : h($_POST['username']);
    $age = (!empty($_POST['age'])) ? h($_POST['age']) : '';
    $tel = (!empty($_POST['tel'])) ? h($_POST['tel']) : '';
    $zip = (!empty($_POST['zip'])) ? h($_POST['zip']) : '';
    $addr = (!empty($_POST['addr'])) ? h($_POST['addr']) : '';
    $email = (!empty($_POST['email'])) ? h($_POST['email']) : '';
    $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'], 'pic') : '';
    //uploadImgの関数が空で、データベースにpicが登録されている時に、データベースの値を格納する
    $pic = (empty($pic) && !empty($dbUploadData['pic'])) ? $dbUploadData['pic'] : $pic;
    $back_pic = (!empty($_FILES['back_pic']['name'])) ? uploadImg($_FILES['back_pic'], 'back_pic') : '';
    $back_pic = (empty($back_pic) && $dbUploadData['back_pic']) ? $dbUploadData['back_pic'] : $back_pic;

    //データベースの情報とPOSTされてきた情報が違う場合（変更されている場合）のみバリデーションチェックを行う
    // メールアドレス以外は入力必須ではないのでフォームに値があればバリデーションを行う
    debug('バリデーション開始');
    if ($dbUploadData['username'] !== $username && $_POST['username'] !== '') {
        validMaxLen($username, 'username');
    }

    if ($dbUploadData['tel'] !== $tel && ($_POST['tel']) !== '') {
        //TEL形式チェック
        validTel($tel, 'tel');
        validHalf($tel, 'tel');
    }

    if ($dbUploadData['zip'] !== $zip && $_POST['zip'] !== '') {
        //郵便番号形式チェック
        validZip($zip, 'zip');
    }

    if ($dbUploadData['addr'] !== $addr && $_POST['addr'] !== '') {
        // 入力必須ではないのでフォームに値があればバリデーションチェックする
        if (!empty($_POST['addr'])) {
            // 住所最大文字数チェック
            validMaxLen($addr, 'addr');
        }
    }
    if ($dbUploadData['age'] !== $age && $_POST['age'] !== '') {
        // 年齢の半角数字チェック
        validHalfNum($age, 'age');
    }

    if ($dbUploadData['email'] !== $email) {
        // メールアドレスのチェック
        validEmailDup($email, 'email');
        validEmail($email, 'email');
        validMaxLen($email, 'email');
        validMinLen($email, 'email');
        validRequired($email, 'email');
    }

    //バリデーション通過なら
    if (empty($err_msg)) {
        debug('バリデーション通過');
        debug('データベースにプロフィール情報を登録します');

        try {
            $pdo = dbConnect();
            $sql = 'UPDATE users SET username = :username, age = :age, tel = :tel, zip = :zip, addr = :addr, email = :email, pic = :pic, back_pic = :back_pic WHERE id = :u_id';
            $data = array(
                ':username' => $username,
                ':age' => $age,
                ':tel' => $tel,
                ':zip' => $zip,
                ':addr' => $addr,
                ':email' => $email,
                ':pic' => $pic,
                ':back_pic' => $back_pic,
                ':u_id' => $_SESSION['user_id'],
            );

            $stmt = queryPost($pdo, $sql, $data);
            if (!empty($stmt->rowCount())) {
                debug('プロフィールの更新に成功しました');
                $_SESSION['suc_message'] = 'プロフィールを変更しました！';
                $_POST = array();
                debug('マイページへ遷移します');
                header('Location:mypage.php');
                exit();
            } else {
                debug('プロフィールの更新はありませんでした');
                $_SESSION['suc_message'] = 'プロフィールを変更しました！';
                $_POST = array();
                header('Location:mypage.php');
                exit();
            }
        } catch (Exception $e) {
            debug('エラー発生', $e->getMessage());
            errMsg('common', ERR_MSG05);
        }
    } else {
        debug('バリデーションエラーです');
        debug('エラー内容', $err_msg);
    }
}
?>
<?php
$siteTitle = 'プロフィール変更ページ';
require('head.php');
require('header.php');
?>

<main>
    <section id="main" class="l-siteWidth">
        <form action="" method="post" enctype="multipart/form-data" class="p-form p-form--widthWide">
            <div class="p-profEdit">
                <input type="hidden" name="body_scroll_px" class="js-getBodyScrollPx" value="">
                <div class="p-profEdit__imgContainer">
                    <label class="c-dragDropArea c-dragDropArea--homeImg js-dragDropArea <?php if (!empty($err_msg['back_pic'])) echo 'err'; ?>">
                        <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                        背景画像（ドラッグ&ドロップ）
                        <input type="file" name="back_pic" value="<?php echo getFormData('back_pic'); ?>" class="c-dragDropArea__fileInput js-fileInput">
                        <img src="<?php echo getFormData('back_pic'); ?>" class="c-dragDropArea__prevImg js-prevImg" <?php if (empty(getFormData('back_pic'))) echo 'style="display: none;"'; ?>>
                    </label>
                    <label class="c-dragDropArea c-dragDropArea--topImg js-dragDropArea <?php if (!empty($err_msg['pic'])) echo 'u-err'; ?>">
                        <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                        トップ画像
                        <input type="file" name="pic" value="<?php echo getFormData('pic'); ?>" class="c-dragDropArea__fileInput js-fileInput">
                        <img src="<?php echo getFormData('pic'); ?>" class="c-dragDropArea__prevImg js-prevImg" <?php if (empty(getFormData('pic'))) echo 'style="display: none;"'; ?>>
                    </label>
                </div>
                <div class="u-errorMessage p-form--margin">
                    <?php if (!empty($err_msg['pic']) || !empty($err_msg['back_pic'])) {
                        echo $err_msg['pic'];
                        echo $err_msg['back_pic'];
                    } ?>
                </div>
                <label>
                    ユーザーネーム
                    <input type="text" name="username" value="<?php echo getFormData('username'); ?>" class="c-input <?php if (!empty($err_msg['username'])) echo 'u-err'; ?>">
                </label>
                <div class=" u-errorMessage p-form--margin">
                    <?php if (!empty($err_msg['username'])) echo $err_msg['username']; ?>
                </div>
                <label>
                    年齢
                    <select name="age" class="c-selectBox c-selectBox--short <?php if (!empty($err_msg['age'])) echo 'u-err'; ?>">
                        <option value="" <?php if ((int) getFormData('age') == '') echo 'selected'; ?>></option>
                        <?php for ($i = 1; $i < 131; $i++) : ?>
                            <option value="<?php echo $i; ?>" <?php if ((int) getFormData('age') == $i) echo 'selected'; ?>><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </label>
                <div class="u-errorMessage p-form--margin">
                    <?php if (!empty($err_msg['age'])) echo $err_msg['age']; ?>
                </div>
                <label>
                    電話番号 ※ハイフンなしでご入力ください
                    <input type="text" name="tel" value="<?php echo getFormData('tel'); ?>" class="c-input <?php if (!empty($err_msg['tel'])) echo 'u-err'; ?>">
                </label>
                <div class="u-errorMessage p-form--margin">
                    <?php if (!empty($err_msg['tel'])) echo $err_msg['tel']; ?>
                </div>
                <label>
                    郵便番号 ※ハイフンなしでご入力ください
                    <input type="text" name="zip" value="<?php echo getFormData('zip'); ?>" class="c-input <?php if (!empty($err_msg['zip'])) echo 'u-err'; ?>">
                </label>
                <div class="u-errorMessage p-form--margin">
                    <?php if (!empty($err_msg['zip'])) echo $err_msg['zip']; ?>
                </div>
                <label>
                    住所
                    <input type="text" name="addr" value="<?php echo getFormData('addr'); ?>" class="c-input <?php if (!empty($err_msg['addr'])) echo 'u-err'; ?>">
                </label>
                <div class="u-errorMessage p-form--margin">
                    <?php if (!empty($err_msg['addr'])) echo $err_msg['addr']; ?>
                </div>
                <label>
                    メールアドレス<span class="u-require">（必須）</span>
                    <input type="email" name="email" value="<?php echo getFormData('email'); ?>" class="c-input <?php if (!empty($err_msg['email'])) echo 'u-err'; ?>">
                    <div class="u-errorMessage p-form--margin">
                        <?php if (!empty($err_msg['email'])) echo $err_msg['email']; ?>
                    </div>
                </label>
                <div class="c-btnContainer">
                    <input type="submit" value="変更する" class="c-btn c-btn--flRight">
                </div>
        </form>
    </section>
</main>
<?php require('footer.php'); ?>