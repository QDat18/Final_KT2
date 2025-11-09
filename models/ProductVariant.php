<?php
class ProductVariant
{
    private $conn;
    private $table = "product_variants";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getByProductID($product_id)
    {
        $query = "SELECT * FROM {$this->table} WHERE product_id = :product_id ORDER BY color, storage";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":product_id", $product_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByID($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findBySKU($sku)
    {
        $query = "SELECT * FROM {$this->table} WHERE sku = :sku LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":sku", $sku);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function create($data)
    {
        $query = "INSERT INTO {$this->table} 
                    (product_id, sku, color, storage, price, stock, image) 
                  VALUES 
                    (:product_id, :sku, :color, :storage, :price, :stock, :image)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($data);
    }

    public function update($data)
    {
        $query = "UPDATE {$this->table}
                  SET sku=:sku, color=:color, storage=:storage, price=:price, 
                      stock=:stock, image=:image, updated_at=NOW()
                  WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($data);
    }

    public function delete($id)
    {
        $query = "DELETE FROM {$this->table} WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":id", $id);
        return $stmt->execute();
    }

    

    /**
     * Tìm biến thể theo product_id, màu sắc và dung lượng
     * @param int $product_id
     * @param string $color
     * @param string $storage
     * @return array|false
     */
    public function findByProductColorStorage($product_id, $color, $storage)
    {
        $sql = "SELECT * FROM {$this->table} 
            WHERE product_id = :product_id 
            AND color = :color 
            AND storage = :storage 
            LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':product_id' => $product_id,
            ':color' => trim($color),
            ':storage' => trim($storage)
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
 * Kiểm tra biến thể đã tồn tại chưa
 * @param int $product_id
 * @param string $color
 * @param string $storage
 * @return bool
 */
public function variantExists($product_id, $color, $storage) {
    $sql = "SELECT COUNT(*) as count FROM {$this->table} 
            WHERE product_id = :product_id 
            AND LOWER(TRIM(color)) = LOWER(TRIM(:color))
            AND TRIM(REPLACE(storage, ' ', '')) = TRIM(REPLACE(:storage, ' ', ''))";
    
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([
        ':product_id' => (int)$product_id,
        ':color' => $color,
        ':storage' => $storage
    ]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return ($result['count'] > 0);
}
}
