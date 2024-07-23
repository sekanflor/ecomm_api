<?php

class ProductService
{
    private $conn;
    private $table_name = "item";
    private $upload_dir;

    public function __construct($pdo)
    {
        $this->conn = $pdo;
        $this->upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/ECOMM-images/';
    }

    public function createProduct()
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!isset($_FILES['image'])) {
            http_response_code(400);
            return json_encode(["error" => "No image uploaded"]);
        }

        foreach ($_POST as $key => $value) {
            $data[$key] = htmlspecialchars(strip_tags($value));
        }

        if (!isset($data['item_name']) || !isset($data['item_price']) || !isset($data['item_description'])) {
            http_response_code(400);
            return json_encode(["error" => "Null Data"]);
        }

        $item_name = $data['item_name'];
        $item_price = $data['item_price'];
        $item_description = $data['item_description'];
        $image = $_FILES['image'];

        if ($image['error'] === 0) {
            if (in_array($image['type'], ["image/jpeg", "image/png", "image/gif"])) {
                if ($image['size'] <= 500000) { // 500 KB
                    $image_path = $this->upload_dir . uniqid() . '_' . $image['name'];
                    move_uploaded_file($image['tmp_name'], $image_path);
                    $image_name_only = basename($image_path);

                    $query = "INSERT INTO " . $this->table_name . " 
                              SET item_name=:item_name, item_price=:item_price, item_description=:item_description, image=:image";
                    $stmt = $this->conn->prepare($query);

                    $stmt->bindParam(":item_name", $item_name);
                    $stmt->bindParam(":item_price", $item_price);
                    $stmt->bindParam(":item_description", $item_description);
                    $stmt->bindParam(":image", $image_name_only);

                    if ($stmt->execute()) {
                        http_response_code(201);
                        return json_encode(["message" => "Product was created."]);
                    } else {
                        http_response_code(503);
                        return json_encode(["message" => "Unable to create product."]);
                    }
                } else {
                    http_response_code(400);
                    return json_encode(["error" => "Image size exceeds 500 KB"]);
                }
            } else {
                http_response_code(400);
                return json_encode(["error" => "Invalid image type"]);
            }
        } else {
            http_response_code(400);
            return json_encode(["error" => "Image upload failed"]);
        }
    }

    public function readProducts()
    {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY item_description";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $products_arr = ["records" => []];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products_arr["records"][] = $row;
        }

        return json_encode($products_arr);
    }

    public function readOneProduct($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE item_id = :item_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":item_id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            return json_encode($product);
        } else {
            http_response_code(404);
            return json_encode(["message" => "Product not found."]);
        }
    }

    public function updateProduct($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE item_id = :item_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":item_id", $id);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            http_response_code(404);
            return json_encode(["message" => "Product not found."]);
        }

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        $name = isset($_POST['item_name']) ? htmlspecialchars(strip_tags($_POST['item_name'])) : $product['item_name'];
        $price = isset($_POST['item_price']) ? htmlspecialchars(strip_tags($_POST['item_price'])) : $product['item_price'];
        $description = isset($_POST['item_description']) ? htmlspecialchars(strip_tags($_POST['item_description'])) : $product['item_description'];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $image = $_FILES['image'];
            if (in_array($image['type'], ["image/jpeg", "image/png", "image/gif"])) {
                if ($image['size'] <= 500000) { // 500 KB
                    $image_path = $this->upload_dir . uniqid() . '_' . $image['name'];
                    move_uploaded_file($image['tmp_name'], $image_path);
                    $image_name_only = basename($image_path);

                    if ($product['image']) {
                        unlink($this->upload_dir . $product['image']);
                    }
                } else {
                    http_response_code(400);
                    return json_encode(["error" => "Image size exceeds 500 KB"]);
                }
            } else {
                http_response_code(400);
                return json_encode(["error" => "Invalid image type"]);
            }
        } else {
            $image_name_only = $product['image'];
        }

        $query = "UPDATE " . $this->table_name . " 
                  SET item_name=:item_name, item_price=:item_price, item_description=:item_description, image=:image 
                  WHERE item_id=:item_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":item_name", $name);
        $stmt->bindParam(":item_price", $price);
        $stmt->bindParam(":item_description", $description);
        $stmt->bindParam(":image", $image_name_only);
        $stmt->bindParam(":item_id", $id);

        if ($stmt->execute()) {
            http_response_code(200);
            return json_encode(["message" => "Product was updated."]);
        } else {
            http_response_code(503);
            return json_encode(["message" => "Unable to update product."]);
        }
    }

    public function deleteProduct($id)
    {
        $query = "SELECT image FROM " . $this->table_name . " WHERE item_id = :item_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":item_id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $image_path = $row['image'];
            unlink($this->upload_dir . $image_path);
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE item_id = :item_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":item_id", $id);

        if ($stmt->execute()) {
            http_response_code(200);
            return json_encode(["message" => "Product was deleted."]);
        } else {
            http_response_code(503);
            return json_encode(["message" => "Unable to delete product."]);
        }
    }
}
