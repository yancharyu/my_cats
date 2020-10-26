<?php
require('function.php');
startProcess('ajaxページ');


// ========================================
// ajax処理
// ========================================

// postがあり、ユーザーIDがあり、ログインしている場合

if (isset($_POST['uploadId']) && isset($_SESSION['user_id']) && !empty(isLogin())) {

    $up_id = $_POST['uploadId'];
    $u_id = $_SESSION['user_id'];
    debug('ajax通信開始');
    debug('渡された投稿ID', $up_id);
    debug('渡されたユーザーID', $u_id);

    try {

        debug('いいね処理を行います');
        $pdo = dbConnect();
        $sql = 'SELECT * FROM favos WHERE uploads_id = :up_id AND user_id = :u_id';
        $data = array(
            ':up_id' => $up_id,
            'u_id' => $u_id,
        );

        $stmt = queryPost($pdo, $sql, $data);
        if (empty($stmt->rowCount())) {
            debug('まだいいねしていません');
            $boolean = true;


            $sql = 'INSERT INTO favos (uploads_id, user_id, create_date) VALUES (:up_id, :u_id, :date)';
            $data = array(
                ':up_id' => $up_id,
                'u_id' => $u_id,
                ':date' => date('Y-m-d H:i:s'),
            );
            queryPost($pdo, $sql, $data);
        } else {
            debug('既にいいねしています');
            $boolean = false;

            $sql = 'DELETE FROM favos WHERE uploads_id = :up_id AND user_id = :u_id';
            $data = array(
                'up_id' => $up_id,
                'u_id' => $u_id,
            );
            queryPost($pdo, $sql, $data);
        }

        debug('いいね数を取得します');
        $sql = 'SELECT COUNT(id) AS cnt FROM favos WHERE uploads_id = :up_id';
        $data = array(
            ':up_id' => $up_id,
        );
        $stmt = queryPost($pdo, $sql, $data);
        $count = $stmt->fetch()['cnt'];
        $rst = [
            'favosFlg' => $boolean,
            'count' => $count
        ];
        debug('値', $rst);
        echo json_encode($rst, JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        debug('エラーメッセージ', $e->getMessage());
    }
}
