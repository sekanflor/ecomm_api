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

        if (isset($_FILES['image'])) {
            $image = $_FILES['image'];
            // ... (rest of the image validation and upload code)
        } else {
            http_response_code(400);
            return json_encode(["error" => "No image uploaded"]);
        }

        $data = [];
        foreach ($_POST as $key => $value) {
            $data[$key] = htmlspecialchars(strip_tags($value));
        }

        if (!isset($data['item_name']) || !isset($data['item_price']) || !isset($data['item_description'])) {
            http_response_code(400);
            return json_encode(["error" => "Null Data"]);
        }

        $item_name = htmlspecialchars(strip_tags($data['item_name']));
        $item_price = htmlspecialchars(strip_tags($data['item_price']));
        $item_description = htmlspecialchars(strip_tags($data['item_description']));

        if (isset($_FILES['image'])) {
            $image = $_FILES['image'];
            if ($image['error'] === 0) {
                // Validate image type and size
                if (in_array($image['type'], ["image/jpeg", "image/png", "image/gif"])) {
                    if ($image['size'] <= 500000) { // 500 KB
                        // Upload image to server
                        $image_path = $this->upload_dir . uniqid() . '_' . $image['name'];
                        move_uploaded_file($image['tmp_name'], $image_path);

                        // Store only the image name in the database
                        $image_name_only = basename($image_path);

                        // Insert into database
                        $query = "INSERT INTO " . $this->table_name . " 
                              SET name=:name, price=:price, description=:description, image=:image";
                        $stmt = $this->conn->prepare($query);

                        $stmt->bindParam(":item_name", $item_name);
                        $stmt->bindParam(":item_price", $item_price);
                        $stmt->bindParam(":item_description", $item_description);
                        $stmt->bindParam(":image_name", $image_name_only);

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
        } else {
            http_response_code(400);
            return json_encode(["error" => "No image uploaded"]);
        }
    }

    public function readProducts()
    {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id item_description";
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
        // Check if product exists
        $query = "SELECT * FROM " . $this->table_name . " WHERE item_id = :item_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":item_id", $id);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            http_response_code(404);
            return json_encode(["message" => "Product not found."]);
        }

        // Get current product data
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        // Update product data from form-data
        $name = isset($_POST['item_name']) ? htmlspecialchars(strip_tags($_POST['item_name'])) : $product['item_name'];
        $price = isset($_POST['item_price']) ? htmlspecialchars(strip_tags($_POST['item_price'])) : $product['item_price'];
        $description = isset($_POST['item_description']) ? htmlspecialchars(strip_tags($_POST['item_description'])) : $product['itemdescription'];

        // Check if new image is being uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $image = $_FILES['image'];
            if (in_array($image['type'], ["image/jpeg", "image/png", "image/gif"])) {
                if ($image['size'] <= 500000) { // 500 KB
                    // Upload image to server
                    $image_path = $this->upload_dir . uniqid() . '_' . $image['image_name'];
                    move_uploaded_file($image['tmp_name'], $image_path);
                    $image_name_only = basename($image_path);

                    // Delete old image if a new one is uploaded
                    if ($product['image']) {
                        unlink($this->upload_dir . $product['image_name']);
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

        // Update database
        $query = "UPDATE " . $this->table_name . " 
          SET item_name=:item_name, item_price=:item_price, item_description=:item_description, item_image=:item_image 
          WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":item_name", $name);
        $stmt->bindParam(":item_price", $price);
        $stmt->bindParam(":item_description", $description);
        $stmt->bindParam(":image_name", $image_name_only);
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
            $image_path = $row['image_name'];
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

?>