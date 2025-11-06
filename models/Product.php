<?php
class Product
{
    private $conn;
    private $table = "products";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * HÀM MỚI: Đếm tổng số sản phẩm (hỗ trợ lọc)
     * Dùng để tính toán phân trang
     */
    public function countAll($search = '', $min_price = 0, $max_price = 0) {
        $query = "
            SELECT COUNT(*) as total
            FROM (
                SELECT 
                    p.id,
                    MIN(pv.price) as min_price,
                    MAX(pv.price) as max_price
                FROM 
                    {$this->table} p
                LEFT JOIN 
                    product_variants pv ON p.id = pv.product_id
        ";
        
        $params = [];
        $where_clauses = [];

        if (!empty($search)) {
            $where_clauses[] = "(p.name LIKE :search OR p.sku LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($where_clauses)) {
            $query .= " WHERE " . implode(' AND ', $where_clauses);
        }

        $query .= " GROUP BY p.id ";

        $having_clauses = [];
        if ($min_price > 0) {
            $having_clauses[] = "min_price >= :min_price";
            $params[':min_price'] = $min_price;
        }
        if ($max_price > 0) {
            $having_clauses[] = "max_price <= :max_price";
            $params[':max_price'] = $max_price;
        }

        if (!empty($having_clauses)) {
            $query .= " HAVING " . implode(' AND ', $having_clauses);
        }

        $query .= ") AS filtered_products"; // Đóng subquery

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    /**
     * HÀM NÂNG CẤP: Thêm $limit và $offset cho phân trang
     */
    public function getAll($search = '', $min_price = 0, $max_price = 0, $limit = 10, $offset = 0) {
        $query = "
            SELECT 
                p.id, p.sku, p.name, p.image, p.created_at,
                SUM(pv.stock) as total_stock,
                MIN(pv.price) as min_price,
                MAX(pv.price) as max_price
            FROM 
                {$this->table} p
            LEFT JOIN 
                product_variants pv ON p.id = pv.product_id
        ";
        
        $params = [];
        $where_clauses = [];

        if (!empty($search)) {
            $where_clauses[] = "(p.name LIKE :search OR p.sku LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($where_clauses)) {
            $query .= " WHERE " . implode(' AND ', $where_clauses);
        }

        $query .= " GROUP BY p.id ";

        $having_clauses = [];
        if ($min_price > 0) {
            $having_clauses[] = "min_price >= :min_price";
            $params[':min_price'] = $min_price;
        }
        if ($max_price > 0) {
            $having_clauses[] = "max_price <= :max_price";
            $params[':max_price'] = $max_price;
        }

        if (!empty($having_clauses)) {
            $query .= " HAVING " . implode(' AND ', $having_clauses);
        }

        $query .= " ORDER BY p.created_at DESC ";
        
        // Thêm LIMIT và OFFSET
        $query .= " LIMIT :limit OFFSET :offset";
        
        // PDO cần bind giá trị cho LIMIT/OFFSET một cách đặc biệt
        $stmt = $this->conn->prepare($query);

        // Bind các tham số search/filter
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        
        // Bind các tham số LIMIT/OFFSET
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt;
    }

    // --- CÁC HÀM KHÁC GIỮ NGUYÊN ---

    public function findBySKU($sku) {
        $query = "SELECT * FROM {$this->table} WHERE sku = :sku LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":sku", $sku);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByID($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $query = "INSERT INTO {$this->table} (sku, name, description, image) 
                  VALUES (:sku, :name, :description, :image)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($data);
    }

    public function update($data) {
        $query = "UPDATE {$this->table}
                  SET sku=:sku, name=:name, description=:description, image=:image, 
                      updated_at=NOW()
                  WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($data);
    }

    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":id", $id);
        return $stmt->execute();
    }
}
?>