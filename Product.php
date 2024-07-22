<?php
class Product {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getProducts($articles) {
        $placeholders = implode(',', array_fill(0, count($articles), '?'));
        $types = str_repeat('s', count($articles));
        $sql = "SELECT * FROM Bapmic_cross WHERE article_from_buyer IN ($placeholders)";
        return $this->db->query($sql, $articles, $types);
    }
}
?>
