<?php
require_once "global.php";

class Get extends GlobalMethods {

    private $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function fetchItems() {
        $sqlString = "SELECT * FROM item";
        $stmt = $this->pdo->prepare($sqlString);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($result)) {
            return $this->getResponse($result, "Success", null, 200);
        } else {
            return $this->getResponse(null, "Failed", "Failed to retrieve", 404);
        }
    }

    public function fetchOrderById($orderId) {
        $sqlString = "SELECT * FROM `order` WHERE order_id = ?";
        $stmt = $this->pdo->prepare($sqlString);
        $stmt->bindParam(1, $orderId);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($result)) {
            return $this->getResponse($result, "Success", null, 200);
        } else {
            return $this->getResponse(null, "Failed", "Failed to retrieve", 404);
        }
    }

}
?>
