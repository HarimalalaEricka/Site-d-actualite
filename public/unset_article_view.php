<?php
// public/unset_article_view.php
session_start();
if (isset($_POST['article_id'])) {
    $articleId = (int) $_POST['article_id'];
    $sessionKey = 'article_viewed_' . $articleId;
    unset($_SESSION[$sessionKey]);
    echo json_encode(['success' => true]);
    exit;
}
echo json_encode(['success' => false]);
