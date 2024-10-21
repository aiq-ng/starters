<?php

namespace Controllers;

use Models\Inventory;
use Models\Warehouse;
use Models\Product;


class InventoryController extends BaseController {
    private $inventory;
    private $warehouse;
    private $product;


    public function __construct()
    {
        parent::__construct();
        $this->inventory = new Inventory();
        $this->warehouse = new Warehouse();
        $this->product = new Product();
    }
    
    // Get All Inventory Plans with Warehouse Name
    public function getInventory()
    {
        $inventory_plans = $this->inventory->getAllInventoryPlans();

        if ($inventory_plans) {
            $this->sendResponse('success', 200, $inventory_plans);
        } else {
            $this->sendResponse('Inventory plan not found', 400);
        }
    }
    
    // Get Inventory Items Grouped by Status (e.g., Available, Depleting, KIV)
    public function getInventoryByStatus($status)
    {
        $plans = $this->inventory->getAllInventoryPlans();
        $filteredPlans = array_filter($plans, function ($plan) use ($status) {
            return strtolower($plan['status']) === strtolower($status);
        });

        if ($plans) {
            $this->sendResponse('success', 200, array_values($filteredPlans));
        } else {
            $this->sendResponse('Inventory plan not found', 400);
        }

    }


    // Create a New Inventory Plan
    public function createInventoryPlan($data)
    {
        $planId = $this->inventory->createInventoryPlan($data);
        if ($planId) {
            $this->sendResponse('Inventory Plan created',200, $planId);
        } else {
            $this->sendResponse('Failed to create Inventory Plan',400);
            
        }
    }

  

    public function getInventoryPlan($id)
    {
        $plan = $this->inventory->getInventoryPlan($id);
        if ($plan) {
            $this->sendResponse('success', 200, $plan);
        } else {
            $this->sendResponse('Inventory Plan not found', 400);
        }
    }

     // Update an Existing Inventory Plan
     public function updateInventoryPlan($id, $data)
     {
         $updated = $this->inventory->update($id, $data);
         if ($updated) {
            $this->sendResponse('success',200, $updated);
         } else {
             $this->sendResponse('Failed to update Inventory Plan',400);
         }
     }

      // Get Stock Levels and Progress for a Warehouse
    public function getStockProgress($warehouseId)
    {
        $inventory = $this->inventory->getInventoryByWarehouse($warehouseId);
        if ($inventory) {
            $this->sendResponse('success',200, array_values($inventory));
        }
        else {
            $this->sendResponse('Inventory not found', 400);
        }
    }

    // Update Stock Levels and Progress Bar for a Product in a Warehouse
    public function updateStockProgress($warehouseId, $productId, $quantity, $progress)
    {
        $updated = $this->inventory->updateStock($warehouseId, $productId, $quantity, $progress);
        if ($updated) {
            $this->sendResponse('Stock updated successfully',200,$updated);
           
        } else {
            $this->sendResponse('Failed to update stock',400);
           
        }
    }

    //Search through Inventory plans
    public function searchInventory($keyword)
    {
        $plans = $this->inventory->getAllInventoryPlans();
        $filtered = array_filter($plans, function ($plan) use ($keyword) {
            return stripos($plan['name'], $keyword) !== false;
        });

        if ($filtered) {
            $this->sendResponse('Stock updated successfully',200, array_values($filtered));
           
        } else {
            $this->sendResponse('Failed to update stock',400);
           
        }


    }


}



    
   

   

   