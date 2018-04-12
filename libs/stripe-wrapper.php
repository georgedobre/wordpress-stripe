<?php

	class StripeWrapper {
		public $result = null;
		
		public function __construct($pk = null, $sk = null) {
			try {
				\Stripe\Stripe::setApiKey("sk_test_8ynQdwhwMvjm4acKa907tFas");
				return true;
			} catch(Exception $ex) {
				return false;
			} 
		}
		
		public function getCustomers() {
			try {
				$customers = \Stripe\Customer::all(array("limit" => 10));
				$this->result = $customers->data;
				return true;
			} catch(Exception $ex) {
				return false;
			}
		}
		
		public function create_user($description, $email) {
			try {
				$customers = \Stripe\Customer::create(array(
					'description' => $description,
					'email'	=>	$email
				));
				$this->result = $customers->id;
				return true;
			} catch(Exception $ex) {
				return false;
			}
		}
	}
	
	