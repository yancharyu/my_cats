<?php

//=======================================
// エラー

//E_STRICTレベル以外のエラーを報告する

error_reporting(E_ALL);
// 画面にエラーを表示させるか
ini_set('display_errors', 'On');

//=======================================
// ログ

// ログを取るかどうか
ini_set('log_errors', 'On');
//ログの出力先ファイルを指定
ini_set('error_log', '/Applications/MAMP/htdocs/my_webservice/php.log');

//=======================================
// デフォルトのタイムゾーンを変更
date_default_timezone_set("Asia/Tokyo");

//=======================================
// デバッグ



// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// このデバッグフラグがtrueの時だけログを出力する（開発が終わったらfalseに変える!!!!!!!!!!）
$debug_flg = true;
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!


/**
 * デバッグログ関数
 * @param string $str デバックする文字本文
 * @param string|int|array|object $data SQLの結果などのデータ
 * @return void
 */
function debug(string $str, $data = null): void
{
    global $debug_flg;
    // デバッグフラグがtrueの時
    if (!empty($debug_flg)) {
        //$dataの値がセットされていない時(第二引数が渡されていない時)
        if (!isset($data)) {
            // エラー文だけログに出力する
            error_log($str);
        } else {
            // エラー文と第二引数で渡されたデータもログに表示させる
            error_log($str . '： ' . print_r($data, true));
        }
    }
}

/**
 * var_dumpの関数を見やすくする関数
 * @param mixed $data var_dumpする変数
 * @return void
 */
function dump($data): void
{
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}

// ======================================
// セッション準備 セッション有効期限を伸ばす

// セッションの保存時間
$session_life_time = 60 * 60 * 24 * 30;
// セッションファイルの置き場所を変更
session_save_path('/var/tmp');

// ガーベージコレクションが削除するセッションの有効期限を設定（30日以上経っているものに対してだけ100分の一の確率で削除）
ini_set('session.gc_maxlifetime', $session_life_time);
//ブラウザを閉じてもセッションが削除されないようにクッキー自体の有効期限を伸ばす
ini_set('session_cookie_lifetime', $session_life_time);
//セッションを開始
session_start();
//========================================

/**
 * 画面表示処理ログ吐き出し関数
 * @return void
 */

function debugLogStart(): void
{
    debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');
    debug('画面表示処理開始');
    //現在のセッションIDを取得する
    debug('セッションID', session_id());
    debug('セッション変数の中身', $_SESSION);
    debug('現在日時タイムスタンプ', date('Y-m-d'));
    if (!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])) {
        debug('ログイン期限日時タイムスタンプ', $_SESSION['login_date'] + $_SESSION['login_limit']);
    }
}
//========================================

/**
 * 処理開始関数
 * @param string $title そのページのタイトル
 * @return void
 */
function startProcess(string $title): void
{
    debug('===================================');
    debug($title);
    debugLogStart();
}

/**
 * セッション削除関数
 * @return void
 */
function deleteSession(): void
{
    // セッション変数にからの配列を代入
    $_SESSION = array();
    if (isset($_COOKIE["PHPSESSID"])) {
        setcookie("PHPSESSID", '', time() - 1800, '/');
    }
    // セッションを完全に削除
    session_destroy();
}

//遷移してきた前のページの情報を取得する関数
// function getUrl()
// {
//   debug('前の情報のURL情報を取得します');
//   //ホスト名取得
//   $h = $_SERVER['HTTP_HOST'];
//   // リファラ値があれば、かつ外部サイトでなければaタグで戻るリンクを表示
//   if (!empty($_SERVER['HTTP_REFERER']) && (strpos($_SERVER['HTTP_REFERER'], $h) !== false)) {
//     $url = $_SERVER['HTTP_REFERER'];
//     debug('URL情報の取得に成功');
//     debug('取得したURL', $url);
//     return $url;
//   } else {
//     // 不正な値であればホームページに遷移する
//     debug('前ページのURLが不正です');
//     debug('ホームページに遷移します');
//     echo '不正です';
//     header('Location:home.php');
//     exit();
//   }
// }
//エラーメッセージを定数に格納
define('ERR_MSG01', '入力必須です');
define('ERR_MSG02', 'Emailの形式が違います');
define('ERR_MSG05', '予期せぬエラーが発生しました。時間を置いてからもう一度やり直してください');
define('ERR_MSG06', '入力されたメールアドレスは既に使用されています');
define('ERR_MSG07', '新しいパスワードと一致しません');
define('ERR_MSG08', '半角英数字のみご利用いただけます');
define('ERR_MSG09', 'パスワードは半角英数字のみ8文字以上で入力してください');
define('ERR_MSG10', 'メールアドレス又はパスワードが間違っています');
define('ERR_MSG11', '電話番号の形式が違います');
define('ERR_MSG12', '郵便番号の形式が違います');
define('ERR_MSG13', '半角数字のみご利用いただけます');
define('ERR_MSG14', 'ご年齢を正しく入力してください');
define('ERR_MSG15', 'ハイフン抜きで入力してください');
define('ERR_MSG16', '変更箇所がありません');
define('ERR_MSG17', '現在のパスワードが間違っています');
define('ERR_MSG18', '新しいパスワードが現在のパスワードと同じです');
define('ERR_MSG19', '認証キーが一致しません');
define('ERR_MSG20', '認証キー有効期限が過ぎています');

//エラーメッセージ用の配列を用意
$err_msg = array();

// =================================================
//バリデーション関数を定義

/**
 * $err_msgにメッセージを代入する関数
 * @param string $key エラーメッセージの配列に代入するキー
 * @param string $msg エラーメッセージの内容
 * @return void
 */
function errMsg(string $key, string $msg): void
{
    if (!isset($err_msg)) {
        global $err_msg;
    }
    $err_msg[$key] = $msg;
}

/**
 * 未入力チェック
 * @param string $str バリデーションを行う文字列
 * @param string $key エラーメッセージの配列に代入するキー
 * @return void
 */
function validRequired(string $str, string $key): void
{
    // 空文字のみエラーを出す
    if ($str === '') {
        errMsg($key, ERR_MSG01);
    }
}

/**
 * 最大文字数チェック
 * @param string $str 文字数を計算する文字列
 * @param string $len 最大文字数
 * @return void
 */
function validMaxLen(string $str, string $key, int $len = 255): void
{
    if (mb_strlen($str) > $len) {
        errMsg($key, "{$len}文字以内で入力してください");
    }
}

/**
 * 最小文字数チェック
 * @param string $str 文字数を計算する文字列
 * @param string $key エラーメッセージの配列に代入するキー
 * @return void
 */
function validMinLen(string $str, string $key, int $len = 6): void
{
    if (mb_strlen($str) < $len) {
        errMsg($key, "{$len}文字以内で入力してください");
    }
}

/**
 * 同値チェック
 * @param mixed $str1 文字列
 * @param mixed $str2 文字列
 * @param string $key エラーメッセージの配列に代入するキー
 * @param string $msg エラーメッセージ
 * @return void
 */
function validMatch(string $str1, string $str2, string $key, string $msg): void
{
    if ($str1 !== $str2) {
        errMsg($key, $msg);
    }
}

/**
 * 非同値チェック
 * @param string $str 文字列
 * @param string $str 文字列
 * @param string $key エラーメッセージの配列に代入するキー
 * @param string $msg エラーメッセージ
 * @return void
 *
 */
function validUnMatch(string $str1, string $str2, string $key, string $msg): void
{
    if ($str1 === $str2) {
        errMsg($key, $msg);
    }
}

/**
 * 半角英数字チェック
 * @param string $str 半角英数字かチェックする文字列
 * @param string $key エラーメッセージの配列に代入するキー
 * @return void
 */
function validHalf(string $str, string $key): void
{
    if (!preg_match("/^[0-9a-zA-Z]*$/", $str)) {
        errMsg($key, ERR_MSG08);
    }
}

/**
 * パスワード用バリデーション（半角英数字8文字以上のみ）
 * @param string|int $str パスワード
 * @param string エラーメッセージの配列に代入するキー
 * @return void
 */
function validPassHalfLen(string $str, string $key): void
{
    if (!preg_match("/^([a-zA-Z0-9]{8,})$/", $str)) {
        errMsg($key, ERR_MSG09);
    }
}

/**
 * 現在のパスワードがマッチするかどうか
 * @param string|int $pass1 現在のパスワード
 * @param string|int $pass2 新しいパスワード
 * @return void
 */
function validMatchPass(string $pass1, string $pass2, string $key): void
{
    if (password_verify($pass1, $pass2)) {
        errMsg($key, ERR_MSG17);
    }
}

/**
 * メールアドレス形式チェック
 * @param string|int $str メールアドレス
 * @param string $key エラーメッセージの配列に代入するキー
 * @return void
 */
function validEmail(string $str, string $key): void
{
    if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)) {
        errMsg($key, ERR_MSG02);
    }
}

/**
 * Email重複チェック
 * @param string|int $email メールアドレス
 * @param string $key エラーメッセージの配列に代入するキー
 * @return void
 */
function validEmailDup(string $email, string $key): void
{
    //例外処理
    try {
        debug('メールアドレス重複チェックをします');
        $pdo = dbConnect();
        $sql = 'SELECT count(id) AS dup FROM users WHERE email = :email AND delete_flg = :num';
        $data = array(':email' => $email, ':num' => 0);
        $stmt = queryPost($pdo, $sql, $data);
        $rst = $stmt->fetch()['dup'];

        // 結果が0位上の数字の場合はエラー
        if (!empty($rst)) {
            errMsg($key, ERR_MSG06);
        }
    } catch (Exception $e) {
        debug('エラーが発生しました', $e->getMessage());
        errMsg('common', ERR_MSG05);
    }
}

/**
 * 電話番号形式チェック
 * @param int $str 電話番号
 * @param string $key エラーメッセージの配列に代入するキー
 * @return void
 */
function validTel(string $str, string $key): void
{
    if (!preg_match("/^[0-9]{2,4}[0-9]{2,4}[0-9]{3,4}$/", $str)) {
        errMsg($key, ERR_MSG11);
    }
}

/**
 * 郵便番号形式チェック
 * @param int $str 郵便番号形式チェック
 * @param string $key エラーメッセージの配列に代入するキー
 * @return void
 */
function validZip(string $str, string $key): void
{
    if (!preg_match("/[0-9]{3}?[0-9]{4}/", $str)) {
        errMsg($key, ERR_MSG12);
    }
}

/**
 * 半角数字チェック
 * @param int $str チェックする数字n
 * @param string $key エラーメッセージの配列に代入するキー
 * @return void
 */
function validHalfNum(string $str, string $key): void
{
    if (!preg_match("/^[0-9]+$/", $str)) {
        errMsg($key, ERR_MSG13);
    }
}

/**
 * 固定長チェック
 * @param string $str 文字列
 * @param string $key エラーメッセージの配列に代入するキー
 * @param int $len 固定長をチェックする文字数
 * @return void
 */
function validLength(string $str, string $key, int $len = 8): void
{
    if (mb_strlen($str) !== $len) {
        errMsg($key, "{$len}文字以上で入力してください");
    }
}
/**
 * エラーメッセージを全て取得
 * @param string $key エラーメッセージを取得するキー
 * @return array
 */
function getErrMsg(string $key): ?array
{
    global $err_msg;
    if (!empty($err_msg[$key])) {
        return $err_msg[$key];
    }
}

/**
 * 指定したユーザーの投稿を取得する関数
 * @param int $u_id 取得する投稿のユーザーID
 * @param int $page ページネーション用のパラメータ
 * @param int $span 投稿を何件取得するか
 * @return array
 *
 */
function getUploads(int $u_id, int $page, int $span = 4): ?array
{
    debug('投稿データの投稿数を取得します');
    $currentMinNum = ($page - 1) * $span;
    try {
        $pdo = dbConnect();
        $sql = 'SELECT COUNT(id) AS cnt FROM uploads WHERE user_id = :id AND delete_flg = :num';
        $data = array(
            ':id' => $u_id,
            ':num' => 0,
        );
        $stmt = queryPost($pdo, $sql, $data);

        if (!empty($stmt)) {
            // トータルの投稿数
            $rst['total'] = $stmt->fetch()['cnt'];
            // 現在のページ数
            $rst['total_page'] = ceil($rst['total'] / 4);
            debug('取得した投稿数', $rst['total']);
            debug('総ページ数', $rst['total_page']);
        } else {
            debug('投稿数の取得に失敗');
            return false;
        }

        $sql = 'SELECT up.*, u.username, u.pic, u.back_pic,  b.name AS bre_name FROM uploads AS up LEFT JOIN users AS u ON up.user_id = u.id LEFT JOIN breed AS b ON up.breed_id = b.id WHERE up.user_id = :id AND up.delete_flg = :num  ORDER BY create_date DESC LIMIT :span OFFSET :page';
        $data = array(
            ':id' => $u_id,
            ':num' => 0,
            ':span' => $span,
            ':page' => $currentMinNum,
        );
        $stmt = queryPost($pdo, $sql, $data);
        $rst['data'] = $stmt->fetchAll();

        if (!empty($rst)) {
            debug('投稿一覧取得に成功しました');
            debug('取得した投稿データ', $rst);
            return $rst;
        } else {
            debug('投稿取得に失敗しました');
        }
    } catch (Exception $e) {
        debug('エラー発生', $e->getMessage());
    }
}

/**
 * 投稿を一つ取得する関数  (詳細画面表示など)
 * @param int $up_id 取得する投稿のID
 * @return array
 */
function getUploadsOne(int $up_id): ?array
{
    debug('投稿データを一つ取得します');
    try {
        $pdo = dbConnect();
        $sql = 'SELECT up.*, u.username, u.pic, u.back_pic, b.name AS bre_name FROM uploads AS up LEFT JOIN users AS u ON up.user_id = u.id LEFT JOIN breed AS b ON up.breed_id = b.id WHERE up.id = :id AND up.delete_flg = :num ORDER BY create_date DESC';
        $data = array(':id' => $up_id, ':num' => 0);

        $stmt = queryPost($pdo, $sql, $data);
        $rst = $stmt->fetchAll();

        if (!empty($rst)) {
            debug('投稿情報の取得に成功');
            debug('取得した投稿', $rst);
            return $rst;
        } else {
            debug('投稿情報の取得に失敗しました');
        }
    } catch (Exception $e) {
        debug('エラー発生', $e->getMessage());
    }
}

/**
 * 掲示板とコメントを取得する関数
 * @param int $up_id 取得する投稿のID
 * @param int $page ページネーション用のパラメータ
 * @param int 何件取得するかのパラメータ
 * @return array
 */
function getComments(int $up_id, int $page, int $span = 5): ?array
{
    $currentMinNum = ($page - 1) * $span;
    debug('掲示板情報を取得します');
    debug('メッセージの数を取得します');
    try {
        $pdo = dbConnect();
        $sql = 'SELECT * FROM board WHERE uploads_id = :up_id AND delete_flg = :num';
        $data = array(':up_id' => $up_id, ':num' => 0);
        $stmt = queryPost($pdo, $sql, $data);
        if (!empty($stmt)) {
            debug('掲示板情報の取得に成功');
            $rst['board'] = $stmt->fetch();
        } else {
            debug('掲示板情報の取得に失敗');
            return false;
        }

        // コメントの数を取得
        $sql = 'SELECT COUNT(id) AS cnt FROM comments WHERE board_id = :b_id AND delete_flg = :num';
        $data = array(
            ':b_id' => $rst['board']['id'],
            ':num' => 0,
        );
        $stmt = queryPost($pdo, $sql, $data);
        if (!empty($stmt)) {
            $rst['total'] = $stmt->fetch()['cnt'];
            $rst['total_page'] = ceil($rst['total'] / 5);
            debug('取得したコメント数', $rst['total']);
            debug('取得した総ページ数', $rst['total_page']);
        } else {
            debug('コメント数の取得に失敗');
            return false;
        }

        $sql = 'SELECT c.*, u.username, u.pic, u.id AS user_id FROM comments AS c LEFT JOIN users AS u ON c.user_id = u.id WHERE c.board_id = :b_id AND c.delete_flg = :num ORDER BY c.send_date DESC LIMIT :span OFFSET :page';
        $data = array(
            ':b_id' => $rst['board']['id'],
            ':num' => 0,
            ':span' => $span,
            'page' => $currentMinNum,
        );

        $stmt = queryPost($pdo, $sql, $data);
        if (!empty($stmt)) {
            debug('コメントの取得に成功');
            $rst['comments'] = $stmt->fetchAll();
            debug('取得したデータ', $rst);
            return $rst;
        } else {
            debug('コメントの取得に失敗');
            return false;
        }
    } catch (Exception $e) {
        debug('エラー発生', $e->getMessage());
    }
}

/**
 * ページネーション用の数字と投稿を全て取得する関数
 * @param int $page 取得するページのID
 * @param int $b_id 検索用のID
 * @param int $sort 並び順
 * @param int $span 投稿を何件取得するか（デフォルトは6）
 */
function getUploadLists(int $page, string $b_id = null, int $sort, int $span = 6): ?array
{
    $currentMinNum = ($page - 1) * $span;
    debug('指定されたページの投稿を取得します');

    try {
        debug('指定されたページ', $page);
        $pdo = dbConnect();
        $sql = 'SELECT COUNT(id) AS cnt FROM uploads ';

        // 種類の指定がある場合（第2引数）
        if (!empty($b_id)) {
            $sql .= 'WHERE breed_id = :b_id AND delete_flg = :num';
            $data = array(':b_id' => $b_id, ':num' => 0);
        } else {
            $sql .= 'WHERE delete_flg = :num';
            $data = array(':num' => 0);
        }

        $stmt = queryPost($pdo, $sql, $data);

        if (!empty($stmt)) {
            debug('ページ数の取得に成功しました');
            $rst['total'] = $stmt->fetch()['cnt'];
            $rst['total_page'] = ceil($rst['total'] / 6);
            debug('取得した総投稿数', $rst['total']);
            debug('取得した総ページ数', $rst['total_page']);
        } else {
            debug('投稿数の取得に失敗しました');
            return false;
        }

        debug('投稿情報を取得します');
        $sql = 'SELECT up.*, u.username, u.pic, u.back_pic, b.name AS bre_name FROM uploads AS up LEFT JOIN users AS u ON up.user_id = u.id LEFT JOIN breed AS b ON up.breed_id = b.id ';

        // 種類の指定がある場合（第2引数）
        if (!empty($b_id)) {
            $sql .= 'WHERE up.breed_id = :b_id AND up.delete_flg = :num ';
        } else {
            $sql .= 'WHERE up.delete_flg = :num ';
        }

        switch ($sort) {
            case 1:
                $sql .= 'ORDER BY up.create_date DESC ';
                break;
            case 2:
                $sql .= 'ORDER BY up.create_date ASC ';
                break;
        }

        $sql .= 'LIMIT :span OFFSET :page';
        debug('商品取得SQLの中身', $sql);

        $stmt = $pdo->prepare($sql);
        if (!empty($b_id)) {
            $stmt->bindValue(':b_id', $b_id, PDO::PARAM_INT);
        }
        $stmt->bindValue(':num', 0, PDO::PARAM_INT);
        $stmt->bindValue(':page', $currentMinNum, PDO::PARAM_INT);
        $stmt->bindValue(':span', $span, PDO::PARAM_INT);
        $stmt->execute();

        if (!empty($stmt)) {
            debug('商品情報の取得に成功しました');
            $rst['data'] = $stmt->fetchAll();
            debug('$rstの値', $rst);
            return $rst;
        } else {
            debug('商品情報の取得に失敗しました');
        }
    } catch (Exception $e) {
        debug('エラーが発生しました', $e->getMessage());
    }
}

/**
 * データベースからユーザー情報を取得する関数
 * @param int $id 取得するユーザーのID
 * @return array
 */
function getUser(int $id): ?array
{
    debug('ユーザー情報を取得します');
    try {
        $pdo = dbConnect();
        $sql = 'SELECT * FROM users WHERE id = :id AND delete_flg = :num';
        $data = array(':id' => $id, ':num' => 0);

        $stmt = queryPost($pdo, $sql, $data);
        $rst = $stmt->fetchAll();
        if (!empty($rst)) {
            debug('ユーザー情報の取得に成功しました');
            debug('取得したユーザー情報', $rst);
            return $rst;
        } else {
            debug('ユーザー情報の取得に失敗しました');
        }
    } catch (Exception $e) {
        debug('エラー発生', $e->getMessage());
    }
}

/**
 * 現在のパスワードを取得する関数
 * @param int $id ユーザーID
 * @return array
 */
function getPass(int $id): ?array
{
    debug('現在のパスワードを取得します');
    try {
        $pdo = dbConnect();
        $sql = 'SELECT id, password FROM users WHERE id = :id AND delete_flg = :num';
        $data = array(':id' => $id, ':num' => 0);
        $stmt = queryPost($pdo, $sql, $data);
        $rst = $stmt->fetch();
        debug('取得した情報', $rst);

        if (!empty($rst)) {
            debug('パスワードの取得に成功しました');
            return $rst;
        } else {
            debug('パスワードの取得に失敗しました');
            errMsg('common', ERR_MSG05);
        }
    } catch (Exception $e) {
        debug('エラーが発生しました', $e->getMessage());
    }
}

/**
 * カテゴリー取得関数
 * @return array
 */
function getBreed(): ?array
{
    debug('猫の種類を取得します');
    $pdo = dbConnect();
    $sql = 'SELECT * FROM breed WHERE delete_flg = :num';
    $data = array(':num' => 0);

    $stmt = queryPost($pdo, $sql, $data);
    $rst = $stmt->fetchAll();

    if (!empty($rst)) {
        debug('猫の種類を取得しました');
        debug('取得した', $rst);
        return $rst;
    } else {
        debug('猫の種類を取得できませんでした');
    }
}

/**
 * 写真アップロードの関数
 * @param object $file 画像ファイル
 * @param string $key エラーメッセージの配列に代入するキー
 * @return string ファイルのパス
 */

function uploadImg(object $file, string $key): string
{
    debug('画像アップロード開始');
    debug('ファイル情報', $file);
    debug('ファイルエラーチェック開始');
    //ファイルのエラー内容が空か、その値が数値以外の場合には関数を終了
    if (!isset($file['error']) || !is_int($file['error'])) {
        debug('ファイルのエラー内容が適切ではありません');
        debug('関数を終了します');
        return false;
    }

    debug('ファイルエラーの内容', $file['error']);

    try {
        //バリデーション
        // $file['error']の値を確認。配列内には「UPLOAD_ERR_OK」などの定数が入っている
        //「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として0や1などの数値が入っている
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                debug('ファイルエラーなし');
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('ファイルサイズが大きすぎます');
                break;
            case UPLOAD_ERR_PARTIAL:
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('ファイルが正常にアップロードされませんでした');
                break;
            default:
                throw new RuntimeException('その他のエラーが発生しました');
                break;
        }
        //$file['mime']値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
        //exif_imagetype関数は「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す

        $type = @exif_imagetype($file['tmp_name']);
        $typeArray = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG);
        if (!in_array($type, $typeArray, true)) {
            debug('非対応の画像タイプが送信されました');
            debug('送信されたファイル名', $file['tmp_name']);
            throw new RuntimeException('非対応の画像タイプです。他の画像を選択してください');
        }

        //ファイルデータからSHA-1ハッシュを取ってファイル名を決定し、ファイルを保存する
        //ハッシュ化しておかないと、アップロードされたファイル名そのままで保存してしまうと同じファイル名がアップロードされる可能性があり、DBパスを保存した場合、どっちの画像のパスなのか判断がつかなくなってしまう
        //image_type_to_extension関数はファイルの拡張子を取得するもの

        $path = 'uploads/' . sha1_file($file['tmp_name']) . image_type_to_extension($type);

        if (!move_uploaded_file($file['tmp_name'], $path)) {
            debug('ファイル保存時にエラーが発生しました');
            throw new RuntimeException('ファイル保存時にエラーが発生しました');
        }

        //保存したファイルパスの権限を変更
        chmod($path, 0644);

        debug('ファイルは正常にアップされました');
        debug('ファイルのパス', $path);
        return $path;
    } catch (RuntimeException $e) {
        debug('ファイルエラーが発生しました', $e->getMessage());
        errMsg($key, $e->getMessage());
    }
}

/**
 * フォーム入力保持の関数
 *   emptyではなくissetを使うのはフォームは０や配列として入ってる場合もあり、
 *   emptyはそれを空だと判定してしまうから
 * @param string $key 取得するキー
 * @param bool $flg フラグの真偽値によってメソッドを切り替える
 * @return string
 */
function getFormData($key, $flg = false): ?string
{
    global $dbUploadData, $err_msg;
    // 第二引数のフラグによってGETとPOSTを切り替える
    if ($flg === false) {
        $method = $_POST;
    } elseif ($flg === true) {
        $method = $_GET;
    } else {
        // 第２引数にtrue,false以外の値が入力された場合
        debug('getFormDataの第２引数の値が適切ではありません');
        return false;
    }
    // データベースのデータがある場合
    if (!empty($dbUploadData)) {
        //POSTした時にフォームのエラーがある場合
        if (!empty($err_msg[$key])) {
            //データベースの情報ではなくフォームの情報を表示する
            if (isset($method[$key])) {
                return h($method[$key]);
            } else {
                //ない場合（フォームにエラーがある = POSTされているはずなのでまずあえりえないが）はDBの情報を表示
                return h($dbUploadData[$key]);
            }
        } else {
            //POSTにデータがあり、DBの情報と違う場合（このフォームは変更していてエラーもないが、他のフォームでひっかかっている状態）
            if (isset($method[$key]) && $method[$key] !== $dbUploadData[$key]) {
                return h($method[$key]);
            } else {
                return h($dbUploadData[$key]);
            }
        }
    } else {
        // データベースの情報がない場合、フォームにデータがあれば表示し、なければ何も表示しない
        if (isset($method[$key])) {
            return h($method[$key]);
        } else {
            return false;
        }
    }
}

/**
 * データベースに接続するための情報を取得する関数
 * @return object
 */
function dbConnect(): object
{
    // phpunitを使ってテストコードを書くときは、localhostの後にport番号まで書く必要がある
    $dsn = 'mysql:dbname=my_cats;host=localhost;charset=utf8';
    $user = 'root';
    $password = 'root';
    $options = array(
        // エミュレートモードをOFFにする（sqlからデータを受け取った時にint型がstring型に変換されなくなる）
        PDO::ATTR_EMULATE_PREPARES => false,
        // SQL実行失敗時には例外を投げる
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        //デフォルトフェッチモードを連想配列形式に設定
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
        // バッファードクエリをtrueにしておけばSELECTで得た結果に対してrowCountメソッドを使えるようもなる
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    );
    $pdo = new PDO($dsn, $user, $password, $options);
    debug('dbConnectの結果', $pdo);
    return $pdo;
}

/**
 * クエリを実行する関数
 * @param object $pdo pdoオブジェクト
 * @param string $sql 実行するSQL文
 * @param array $data プレースホルダー
 * @return mixed クエリ実行結果
 */
function queryPost(object $pdo, string $sql, array $data)
{
    debug('クエリを実行します');
    $stmt = $pdo->prepare($sql);
    if (!empty($stmt->execute($data))) {
        //クエリに成功したとき
        debug('クエリに成功しました');
        return $stmt;
    } else {
        debug('クエリに失敗しました');
        debug('失敗したクエリ', $stmt);
        errMsg('common', ERR_MSG05);
    }
}

/**
 * サニタイズ関数
 * @param string $str エスケープする文字列
 * @return string エスケープした文字列
 */
function h(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * セッションメッセージを一度だけ表示する関数
 * @param string $key 表示するセッションのキー
 * @return string
 */
function showSessionMessage(string $key): ?string
{
    if (!empty($_SESSION[$key])) {
        $msg = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $msg;
    }
    return false;
}

/**
 * 画像がなかったらサンプル画像を表示する関数
 * @param string $img 画像のパス
 * @return string 画像があればその画像のパスを、なければNO_IMGのパスを返す
 */
function showImg(string $img): string
{
    if (!empty($img)) {
        return h($img);
    } else {
        return 'img/NO_IMG.png';
    }
}

/**
 * ページネーション関数
 * @param int $currentPageNum 現在のページ
 * @param int $totalPageNum 総ページ数
 * @param string $link 検索用のGETパラメータリンク
 * @return void
 */
function pagination(int $currentPageNum, int $totalPageNum, string $link = ''): void
{
    //総ページ数が5以内の場合は全て表示
    if ($totalPageNum <= 5) {
        $minPageNum = 1;
        $maxPageNum = $totalPageNum;
        //総ページ数が5以上かつ現在のページが3,2,1の場合は1〜5を表示
    } elseif ($currentPageNum <= 3) {
        $minPageNum = 1;
        $maxPageNum = 5;
        //総ページ数が5以上かつ現在のページが総ページ-2,-1,-0の場合はラスト5個を表示
    } elseif ($currentPageNum >= $totalPageNum - 2) {
        $minPageNum = $totalPageNum - 4;
        $maxPageNum = $totalPageNum;
        //それ以外の場合は現在ページの前後2つを表示
    } else {
        $minPageNum = $currentPageNum - 2;
        $maxPageNum = $currentPageNum + 2;
    }

    echo '<div class="p-pagination">';
    echo '<ul class="p-pagination__list">';
    // 現在のページ数が2ページ以上の場合は<<を表示して1ページに目に戻れるようにする
    if ($currentPageNum > 2) {
        echo '<li class="p-pagination__item"><a href="?page=1' . $link . '" class="p-pagination__link">&lt;&lt;</a></li>';
    }
    // 現在のページが1以上の場合は<を表示して1ページ戻るボタンを作る
    if ($currentPageNum > 1) {
        echo '<li class="p-pagination__item"><a href="?page=' . ($currentPageNum - 1) . $link . '" class="p-pagination__link">&lt;</a></li>';
    }
    for ($i = $minPageNum; $i <= $maxPageNum; $i++) {
        echo '<li class="p-pagination__item ';
        if ($currentPageNum == $i) {
            echo 'isActive';
        }
        echo '"><a href="?page=' . $i . $link . '" class="p-pagination__link">' . $i . '</a></li>';
    }

    if ($currentPageNum != $maxPageNum && $maxPageNum >= 2) {
        echo '<li class="p-pagination__item"><a href="?page=' . ($currentPageNum + 1) . $link . '" class="p-pagination__link">&gt;</a></li>';
    }
    if ($currentPageNum < ($totalPageNum - 1) && $maxPageNum > 1) {
        echo '<li class="p-pagination__item"><a href="?page=' . $totalPageNum . $link . '" class="p-pagination__link">&gt;&gt;</a></li>';
    }
    echo '</ul>';
    echo '</div>';
}

/**
 * ユーザーがいいねしているかどうかを取得する関数
 * @param int $u_id ユーザーID
 * @param int $up_id 検索する投稿のID
 * @return bool ユーザーが既にいいねしているかどうかの真偽値
 */
function isFavos(int $u_id, int $up_id): bool
{
    debug('いいねしているかどうかを取得します');
    debug('ユーザーID', $u_id);
    debug('投稿ID', $up_id);
    try {

        $pdo = dbConnect();
        $sql = 'SELECT * FROM favos WHERE uploads_id = :up_id AND user_id = :u_id';
        $data = array(
            ':up_id' => $up_id,
            ':u_id' => $u_id,
        );

        $stmt = queryPost($pdo, $sql, $data);
        if (!empty($stmt->rowCount())) {
            debug('いいねしてました', $stmt->fetch());
            return true;
        } else {
            debug('いいねしてませんでした');
            return false;
        }
    } catch (Exception $e) {
        debug('いいねの取得時にエラーが発生しました', $e->getMessage());
    }
}

/**
 * 投稿のいいね数を全て取得する関数
 * @param int $up_id 検索する投稿のID
 * @return
 */
function getFavosCount(int $up_id)
{
    debug('投稿のいいね数を取得します');

    try {
        $pdo = dbConnect();
        $sql = 'SELECT COUNT(id) AS cnt FROM favos WHERE uploads_id = :up_id';
        $data = array(
            ':up_id' => $up_id,
        );
        $stmt = queryPost($pdo, $sql, $data);
        $rst = $stmt->fetch()['cnt'];
        if (!empty($rst)) {
            debug('いいね数を取得しました');
            debug('取得したいいね数', $rst);
            return $rst;
        } else {
            debug('まだいいねされていない投稿です');
        }
    } catch (Exception $e) {
        debug('いいね数の取得に失敗しました', $e->getMessage());
    }
}

/**
 * ログインしているかどうかの確認
 *   auth.php）の画面遷移しないバージョン
 *   関数の名前の頭にisが入っていたら真偽値がreturnされている慣しがある
 * @return bool ログインしているかどうかの真偽値
 */
function isLogin(): bool
{
    //ログインしている場合
    if (!empty($_SESSION['login_date'])) {
        debug('ログイン済みユーザーです');

        //セッション有効期限が切れていた場合
        if (($_SESSION['login_date'] + $_SESSION['login_limit']) < time()) {
            debug('ログイン有効期限が切れています');
            //セッションを削除して（ログアウトさせる）してログインページへ遷移する
            debug('セッションを削除します');
            deleteSession();
            return false;
            //ログイン有効期限内だった場合
        } else {
            debug('ログイン有効期限内です');
            //ログイン日時を現在のUNIXタイムスタンプに更新
            $_SESSION['login_date'] = time();
            debug('ログイン認証完了');
            return true;
        }
        // 未ログインユーザーの場合
    } else {
        debug('未ログインユーザーです');
        return false;
    }
}

/**
 * ランダムにキーを作成する関数
 * @param int $length 作成するキーの文字数（デフォルトは8）
 * @return string 作成した文字列
 */
function makeRandKey(int $length = 8): string
{
    static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
    $str = '';
    for ($i = 0; $i < $length; ++$i) {
        $str .= $chars[mt_rand(0, mb_strlen($chars))];
    }
    debug('生成した認証キー', $str);
    return $str;
}

/**
 * メール送信
 * @param string $from 送信元のメールアドレス
 * @param string $to 送信先のメールアドレス
 * @param string $subject 題名
 * @param string $comment 本文
 * @return void
 */
function sendMail(string $from, string $to, string $subject, string $comment): void
{
    if (!empty($to) && !empty($subject) && !empty($comment)) {
        // 文字化けしないように設定（現在使っている言語を設定）
        mb_language('Japanese');
        mb_internal_encoding("UTF-8");

        // メールを送信
        $result = mb_send_mail($to, $subject, $comment, "From: " . $from);
        // 送信結果を判定
        if (!empty($result)) {
            debug('メールを送信しました');
        } else {
            debug('【エラー発生】メールの送信に失敗しました。');
        }
    }
}
