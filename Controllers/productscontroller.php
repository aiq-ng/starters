
<?php 

require_once __DIR__ ."/../Model/products.php";

class ProductsController {

    private $product;

    public function __construct() {
        $this->product = new Product();
    }

    public function create() {
        // Get raw JSON input data
        $data = json_decode(file_get_contents('php://input'), true);
    
        

        

        // Check if data is an array of multiple products or a single product
        if (is_array($data)) {
            // Check if the array contains multiple objects (array of products)
            if (isset($data[0]) && is_array($data[0])) {
                // Handle multiple products
                foreach ($data as $product) {
                    // Extract data for each product
                    $name = $product['name'] ?? null;
                    $location = $product['location'] ?? null;
                    $vendor = $product['vendor'] ?? null;
                    $code = $product['code'] ?? null;
                    $price = $product['price'] ?? null;
                    $profit = $product['profit'] ?? null;
                    $margin = $product['margin'] ?? null;
                    $quantity = $product['quantity'] ?? null;
                    $unit = $product['unit'] ?? null;
                    $image_path = $product['image_path'] ?? null;
    
                    // Validate required fields
                    if (empty($name) || empty($location) || empty($vendor) || empty($code) || empty($price) || empty($profit) || empty($margin) || empty($quantity) || empty($unit) || empty($image_path)) {
                        echo json_encode(['message' => 'All fields are required for each product']);
                        return;
                    }
    
                    // Call the create method in the Product model
                    $this->product->create($name, $location, $vendor, $code, $price, $profit, $margin, $quantity, $unit, $image_path);
                }
                echo json_encode(['message' => 'All products created successfully']);
            } else {
                // Handle a single product (when it's just a single object, not an array of products)
                $name = $data['name'] ?? null;
                $location = $data['location'] ?? null;
                $vendor = $data['vendor'] ?? null;
                $code = $data['code'] ?? null;
                $price = $data['price'] ?? null;
                $profit = $data['profit'] ?? null;
                $margin = $data['margin'] ?? null;
                $quantity = $data['quantity'] ?? null;
                $unit = $data['unit'] ?? null;
                $image_path = $data['image_path'] ?? null;
    
                // Validate required fields
                if (empty($name) || empty($location) || empty($vendor) || empty($code) || empty($price) || empty($profit) || empty($margin) || empty($quantity) || empty($unit) || empty($image_path)) {
                    echo json_encode(['message' => 'All fields are required']);
                    return;
                }
    
                // Call the create method in the Product model
                $this->product->create($name, $location, $vendor, $code, $price, $profit, $margin, $quantity, $unit, $image_path);
    
                echo json_encode(['message' => 'Product created successfully']);
            }
        } else {
            echo json_encode(['message' => 'Invalid input data']);
        }
    }
    
       

    // Get All Products
    public function getAll() {
        echo json_encode($this->product->getAll());
    }

    // Get Single Product
    public function get($id) {
        echo json_encode($this->product->get($id));
    }

    // Update Product
    public function update($id) {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($this->product->update($id, $data['name'], $data['location'], $data['vendor'], $data['code'], $data['price'], $data['profit'], $data['margin'], $data['quantity'], $data['unit'], $data['image_path'])) {
            echo json_encode(['message' => 'Product updated successfully']);
        } else {
            echo json_encode(['message' => 'Failed to update product']);
        }
    }

    // Delete Product
    public function delete($id) {
        if ($this->product->delete($id)) {
            echo json_encode(['message' => 'Product deleted successfully']);
        } else {
            echo json_encode(['message' => 'Failed to delete product']);
        }
    }

}

