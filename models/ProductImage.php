<?php 
class ProductImage{
    private $conn;
    private $table = "product_images";

    public function __construct($db){
        $this->conn = $db;
    }

    public function getByProductID($product_id){
        $query = "SELECT * FROM {$this->table} WHERE product_id = :product_id ORDER BY created_at ASC";
        $stmt= $this->conn->prepare($query);
        $stmt->bindValue(":product_id", $product_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addImage($product_id, $image_url){
        $query = "INSERT INTO {$this->table} (product_id, image_url) VALUES (:product_id, :image_url)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":product_id", $product_id, PDO::PARAM_INT);
        $stmt->bindValue(":image_url", $image_url, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function getByID($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteByID($id){
        $img = $this->getByID($id);
        if ($img && !empty($img['image_url']) && file_exists($img['image_url'])) {
            unlink($img['image_url']); 
        }
        
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>