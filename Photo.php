<?php
// Photo.php
class Photo {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getPhotos($article_from_supplier) {
        $sql = "SELECT * FROM photo WHERE article_from_supplier = ?";
        return $this->db->query($sql, [$article_from_supplier], 's');
    }
}
?>
