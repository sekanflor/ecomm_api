<?php

require_once("global.php");

class Post extends GlobalMethods {

    private $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function createOrder($data) {
        try {
            // Start a transaction
            $this->pdo->beginTransaction();

            // Log the incoming data
            error_log(print_r($data, true));

            // Insert each order item into the database
            foreach ($data['orders'] as $order) {
                $query = "INSERT INTO order_table (item_id, item_name, item_price, quantity, total_price) 
                          VALUES (:item_id, :item_name, :item_price, :quantity, :total_price)";
                $stmt = $this->pdo->prepare($query);
                $stmt->bindParam(':item_id', $order['item_id']);
                $stmt->bindParam(':item_name', $order['item_name']);
                $stmt->bindParam(':item_price', $order['item_price']);
                $stmt->bindParam(':quantity', $order['quantity']);
                $stmt->bindParam(':total_price', $order['total_price']);
                $stmt->execute();
            }

            // Commit the transaction
            $this->pdo->commit();

            return [
                'status' => 'success',
                'message' => 'Order created successfully.'
            ];
        } catch (Exception $e) {
            // Roll back the transaction in case of an error
            $this->pdo->rollBack();
            error_log('Error: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            ];
        }
    }

    public function signup($data)
    {
        // Validate input
        if (empty($data['email']) || empty($data['password']) || empty($data['username'])) {
            return [
                'status' => 'error',
                'message' => 'Email, username, and password are required.'
            ];
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return [
                'status' => 'error',
                'message' => 'Invalid email format.'
            ];
        }

        $email = $data['email'];
        $username = $data['username'];
        $password = $data['password'];

        try {
            // Start a transaction
            $this->pdo->beginTransaction();

            // Check if the email or username already exists
            $query = "SELECT * FROM users WHERE email = :email OR username = :username LIMIT 1";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $this->pdo->rollBack();
                return [
                    'status' => 'error',
                    'message' => 'Email or username already exists.'
                ];
            }

            // Hash the password
            

            // Insert the new user
            $query = "INSERT INTO users (email, username, password) VALUES (:email, :username, :password)";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);

            if ($stmt->execute()) {
                // Commit the transaction
                $this->pdo->commit();
                return [
                    'status' => 'success',
                    'message' => 'Signup successful.'
                ];
            } else {
                $this->pdo->rollBack();
                return [
                    'status' => 'error',
                    'message' => 'Signup failed. Please try again.'
                ];
            }
        } catch (Exception $e) {
            // Roll back the transaction in case of an error
            $this->pdo->rollBack();
            return [
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            ];
        }
    }


    public function login(array $data): array {
        if (empty($data['email']) || empty($data['password'])) {
            return [
                'status' => 'error',
                'message' => 'Email and password are required.'
            ];
        }

        $email = trim($data['email']);
        $password = trim($data['password']);
     

        // Log email, but never log passwords or sensitive information
        error_log("Login attempt: email = $email");

        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            error_log("User found: " . print_r($user, true));

            if (($password == $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                return $this->sendPayload(null, 'success', 'Login Success', 200);
            } else {
                error_log("Password mismatch for user: $email");
            }
        } else {
            error_log("User not found: $email");
        }

        return [
            'status' => 'error',
            'message' => 'Invalid email or password.'
        ];
    }

}
?>
