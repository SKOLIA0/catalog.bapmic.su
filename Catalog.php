<?php
// Catalog.php
class Catalog {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getCatalog($article_from_supplier) {
        $sql = "SELECT * FROM Catalog WHERE article_from_supplier = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bind_param('s', $article_from_supplier);
        $stmt->execute();
        $result = $stmt->get_result();
        
        

        return $result;
    }
}
?>
