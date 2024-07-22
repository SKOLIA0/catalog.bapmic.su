<?php
class BapmicData {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getBAPMICData($article_from_supplier) {
        $sql = "SELECT * FROM BAPMIC_DATA WHERE article_from_supplier = ?";
        return $this->db->query($sql, [$article_from_supplier], 's');
    }
}
?>
