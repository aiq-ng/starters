<?php

namespace Controllers;

use Models\Purchase;
use Models\Sale;

class AuthController extends BaseController
{
    private $purchase;
    private $sale;

    public function __construct()
    {
        parent::__construct();
        $this->purchase = new Purchase();
        $this->sale = new Sale();
    }
}
