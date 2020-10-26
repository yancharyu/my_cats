<?php
require('function.php');
startProcess('投稿ページ');
require('auth.php');

$userData = getUser($_SESSION['user_id']);
$breed = getBreed();

if (!empty($_POST)) {
  debug('POST送信があります');

  $name = (!empty($_POST['name'])) ? h($_POST['name']) : '';
  $breed_id = (!empty($_POST['breed_id'])) ? h($_POST['breed_id']) : '';
  $age = (!empty($_POST['age'])) ? h($_POST['age']) : '';
  $comment = (!empty($_POST['comment'])) ? h($_POST['comment']) : '';
  $pic1 = (!empty($_FILES['pic1']['name'])) ? uploadImg($_FILES['pic1'], 'pic1') : '';
  $pic2 = (!empty($_FILES['pic2']['name'])) ? uploadImg($_FILES['pic2'], 'pic2') : '';
  $pic3 = (!empty($_FILES['pic3']['name'])) ? uploadImg($_FILES['pic3'], 'pic3') : '';
  $pic4 = (!empty($_FILES['pic4']['name'])) ? uploadImg($_FILES['pic4'], 'pic4') : '';

  debug('バリデーション開始');

  //名前のバリデーション
  validMaxLen($name, 'name');
  validRequired($name, 'name');

  //種類のバリデーション
  validRequired($breed_id, 'breed_id');
  validRequired($age, 'age');

  //紹介文は必須ではないので入力されている場合のみバリデーションを行う
  if ($comment !== '') {
    validMaxLen($comment, 'comment');
  }

  if (empty($err_msg)) {
    debug('バリデーション通過');

    // データベース登録処理開始
    try {
      $pdo = dbConnect();
      $sql = 'INSERT INTO uploads (name, age, breed_id, comment, pic1, pic2, pic3, pic4, user_id, create_date) VALUES (:name, :age, :breed_id, :comment, :pic1, :pic2, :pic3, :pic4, :user_id, :date)';
      $data = array(
        ':name' => $name,
        ':age' => $age,
        ':breed_id' => $breed_id,
        ':comment' => $comment,
        ':pic1' => $pic1,
        ':pic2' => $pic2,
        ':pic3' => $pic3,
        ':pic4' => $pic4,
        ':user_id' => $_SESSION['user_id'],
        ':date' => date('Y-m-d H:i:s'),
      );
      queryPost($pdo, $sql, $data);
      $up_id = $pdo->lastInsertId();

      if (!empty($up_id)) {
        debug('投稿に成功しました');
        debug('ボード情報を新規登録します');
        $sql = 'INSERT INTO board (uploads_id, create_date) VALUES (:up_id, :date)';
        $data = array(
          ':up_id' => $up_id,
          ':date' => date('Y-m-d H:i:s'),
        );
        $stmt = queryPost($pdo, $sql, $data);

        if (!empty($stmt->rowCount())) {
          debug('ボード情報の取得に成功しました');
          $_SESSION['suc_message'] = '投稿しました！';
          $_POST = array();
          debug('タイムラインへ遷移する');
          header('Location:index.php');
          exit();
        }
      }
    } catch (Exception $e) {
      debug('エラー発生', $e->getMessage());
      errMsg('common', ERR_MSG05);
    }
  } else {
    debug('バリデーションエラー');
    debug('エラー内容', $err_msg);
  }
}
?>

<?php
$siteTitle = '投稿ページ';
require('head.php');
require('header.php');
?>
<main>
  <section id="main" class="site-width">
    <form action="" method="post" class="p-form p-form--widthWide" enctype="multipart/form-data">
      <div class="p-uploadRegist">
        <input type="hidden" name="body_scroll_px" class="js-getBodyScrollPx" value="">
        <p class="p-form__title">新規投稿</p>
        <div class="u-errorMessage p-form--margin">
          <?php if (!empty($err_msg['common'])) echo $err_msg['common']; ?>
        </div>
        <label class="<?php if (!empty($err_msg['name'])) echo 'u-err'; ?>">
          お名前
          <input type="text" name="name" value="<?php echo getFormData('name') ?>" class="c-input">
        </label>
        <div class="u-errorMessage p-form--margin">
          <?php if (!empty($err_msg['name'])) echo $err_msg['name']; ?>
        </div>
        <label class="<?php if (!empty($err_msg['breed_id'])) echo 'u-err'; ?>">
          種類
          <select name="breed_id" class="c-selectBox c-selectBox--widthMax">
            <option value="" <?php if (getFormData('breed_id') == "") echo 'selected'; ?>>選択してください</option>
            <?php if (!empty($breed)) :
              foreach ($breed as $key => $val) :
            ?>
                <option value="<?php echo h($val['id']); ?>" <?php if ((int) getFormData('breed_id') == $val['id']) echo 'selected'; ?>><?php echo h($val['name']); ?></option>
            <?php
              endforeach;
            endif;
            ?>
            <option value="100" <?php if (getFormData('breed_id') == 100) echo 'selected'; ?>>その他</option>
          </select>
        </label>
        <div class="u-errorMessage p-form--margin">
          <?php if (!empty($err_msg['breed_id'])) echo $err_msg['breed_id']; ?>
        </div>
        <label class="num <?php if (!empty($err_msg['age'])) echo 'u-err'; ?>">
          年齢
          <select name="age" class="c-selectBox">
            <option value="" <?php if ((int) getFormData('age') == '') echo 'selected'; ?>></option>
            <option value="1" <?php if ((int) getFormData('age')) echo 'selected'; ?>>1歳未満</option>
            <?php for ($i = 2; $i < 31; $i++) : ?>
              <option value="<?php echo $i; ?>" <?php if ((int) getFormData('age') == $i) echo 'selected'; ?>><?php echo $i; ?></option>
            <?php endfor; ?>
          </select>
        </label>
        <div class="u-errorMessage p-form--margin">
          <?php if (!empty($err_msg['age'])) echo $err_msg['age']; ?>
        </div>
        <label class="<?php if (!empty($err_msg['comment'])) echo 'u-err'; ?>">
          紹介（200文字以内）
          <textarea name="comment" cols="100" rows="10" placeholder="目が大きいのが特徴です" class="c-textBox"><?php echo getFormData('comment'); ?></textarea>
        </label>
        <div class="u-errorMessage p-form--margin">
          <?php if (!empty($err_msg['comment'])) echo $err_msg['comment']; ?>
        </div>
        写真（4枚まで投稿できます）
        <?php for ($i=1; $i < 5; $i++) : ?>
          <label class="c-dragDropArea js-dragDropArea  <?php if (!empty($err_msg['pic1'])) echo 'u-err'; ?>">
            <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
          ドラッグ＆ドロップ
            <input type="file" name="pic1" value="<?php echo getFormData('pic1'); ?>" class="c-dragDropArea__fileInput js-fileInput">
          <img src="<?php echo getFormData('pic1'); ?>" class="c-dragDropArea__prevImg js-prevImg" style="<?php if (empty(getFormData('pic1'))) echo 'display: none;'; ?>">
        </label>
        <div class="u-errorMessage p-form--margin">
          <?php if (!empty($err_msg['pic1'])) echo $err_msg['pic1']; ?>
        </div>
        <div class="js-drop-container js-sub-drop-container">
          <label class="js-drag-drop <?php if (!empty($err_msg['pic2'])) echo 'u-err'; ?>">
            <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
            ドラッグ＆ドロップ
            <input type="file" name="pic2" value="<?php echo getFormData('pic2'); ?>" class="js-input-file">
            <img src="<?php echo getFormData('pic2'); ?>" class="c-dragDropArea__prevImg js-prevImg" style="<?php if (empty(getFormData('pic2'))) echo 'display: none;'; ?>">

          </label>
          <div class="u-errorMessage p-form--margin">
            <?php if (!empty($err_msg['pic2'])) echo $err_msg['pic2']; ?>
          </div>
        </div>
        <div class="js-drop-container js-sub-drop-container">
          <label class="js-drag-drop <?php if (!empty($err_msg['pic3'])) echo 'u-err'; ?>">
            <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
            ドラッグ＆ドロップ
            <input type="file" name="pic3" value="<?php echo getFormData('pic3'); ?>" class="js-input-file">
            <img src="<?php echo getFormData('pic3'); ?>" class="c-dragDropArea__prevImg js-prevImg" style="<?php if (empty(getFormData('pic3'))) echo 'display: none;'; ?>">
          </label>
          <div class="u-errorMessage p-form--margin">
            <?php if (!empty($err_msg['pic3'])) echo $err_msg['pic3']; ?>
          </div>
        </div>
        <div class="js-drop-container js-sub-drop-container">
          <label class="js-drag-drop <?php if (!empty($err_msg['pic4'])) echo 'u-err'; ?>">
            <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
            ドラッグ＆ドロップ
            <input type="file" name="pic4" value="<?php echo getFormData('pic4'); ?>" class="js-input-file">
            <img src="<?php echo getFormData('pic4'); ?>" class="c-dragDropArea__prevImg js-prevImg" style="<?php if (empty(getFormData('pic4'))) echo 'display: none;'; ?>">
          </label>
        </div>
        <div class="u-errorMessage p-form--margin">
          <?php if (!empty($err_msg['pic4'])) echo $err_msg['pic4']; ?>
        </div>
        <div class="btn-container">
          <input type="submit" value="投稿する" class="btn btn-mid">
        </div>
    </form>
  </section>
</main>
<?php require('footer.php'); ?>