
<?php 

require_once __DIR__ ."/../Model/warehouse.php";

class WarehouseController {
    private $wh;

    public function __construct() {
        $this->wh = new Wh();
    }


public function createWh() {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name'] ?? null;
    $address = $data['address'] ?? null;
    if (empty($name) || empty($address)) {
        echo json_encode(['message' => 'All fields are required']);
                return;
     } else {
        $this->wh->createWh($name, $address);
        echo json_encode(['message' => 'Warehouse stored successfully']);
     }
    }


// public function getWh($name) {
//     echo json_encode($this->wh->getWh($name));
// }

}