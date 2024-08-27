<?php
/**
 * Contract ediding class
 *
 * @author ISK
 */

class contracts {

	private $contract_table = '';
	private $employee_table = '';
	private $customer_table = '';
	private $contract_states_table = '';
	private $selected_services_table = '';
	private $parking_lots_table = '';
	private $service_prices_table = '';
    private $services_table = '';

	public function __construct() {
		$this->contract_table = config::DB_PREFIX . 'contracts';
		$this->employee_table = config::DB_PREFIX . 'employees';
		$this->customer_table = config::DB_PREFIX . 'customers';
		$this->contract_states_table = config::DB_PREFIX . 'contract_states';
		$this->parking_lots_table = config::DB_PREFIX . 'parking_lots';
		$this->service_prices_table = config::DB_PREFIX . 'service_prices';
        $this->selected_services_table = config::DB_PREFIX . 'selected_services';
        $this->services_table = config::DB_PREFIX . 'services';
	}
	
	/**
	 * Contract list selection
	 * @param type $limit
	 * @param type $offset
	 * @return type
	 */
	public function getContractList($limit, $offset) {
		$limit = mysql::escapeFieldForSQL($limit);
		$offset = mysql::escapeFieldForSQL($offset);

		$query = "  SELECT `{$this->contract_table}`.`number`,
						   `{$this->contract_table}`.`contract_date`,
						   `{$this->employee_table}`.`name` AS `employee_name`,
						   `{$this->employee_table}`.`surname` AS `employee_surname`,
						   `{$this->customer_table}`.`name` AS `customer_name`,
						   `{$this->customer_table}`.`surname` AS `customer_surname`,
						   `{$this->contract_states_table}`.`name` AS `state`
					FROM `{$this->contract_table}`
						LEFT JOIN `{$this->employee_table}`
							ON `{$this->contract_table}`.`fk_EMPLOYEEemploee_id`=`{$this->employee_table}`.`employee_id`
						LEFT JOIN `{$this->customer_table}`
							ON `{$this->contract_table}`.`fk_CUSTOMERpasport_id`=`{$this->customer_table}`.`pasport_id`
						LEFT JOIN `{$this->contract_states_table}`
							ON `{$this->contract_table}`.`state`=`{$this->contract_states_table}`.`id_CONTRACT_STATES` LIMIT {$limit} OFFSET {$offset}";
		$data = mysql::select($query);
		
		//
		return $data;
	}

    public function lastSelectedServiceID(){
        $query = "SELECT * FROM {$this->selected_services_table} ORDER BY `Id_SELECTED_SERVICE` DESC LIMIT 1";
        mysql::query($query);
        $data = mysql::select($query);
        if(!empty($data)) {
            return $data[0]['Id_SELECTED_SERVICE'];
        }
        return 0;
    }


    /**
	 * Contract count calculation
	 * @return type
	 */
	public function getContractListCount() {
		$query = "  SELECT COUNT(`{$this->contract_table}`.`number`) AS `amount`
					FROM `{$this->contract_table}`
						LEFT JOIN `{$this->employee_table}`
							ON `{$this->contract_table}`.`fk_EMPLOYEEemploee_id`=`{$this->employee_table}`.`employee_id`
						LEFT JOIN `{$this->customer_table}`
							ON `{$this->contract_table}`.`fk_CUSTOMERpasport_id`=`{$this->customer_table}`.`pasport_id`
						LEFT JOIN `{$this->contract_states_table}`
							ON `{$this->contract_table}`.`state`=`{$this->contract_states_table}`.`id_CONTRACT_STATES`";
		$data = mysql::select($query);
		
		//
		return $data[0]['amount'];
	}
	
	/**
	 * Contract selection
	 * @param type $nr
	 * @return type
	 */
	public function getContract($nr) {
		$nr = mysql::escapeFieldForSQL($nr);

		$query = "  SELECT `{$this->contract_table}`.`number`,
						   `{$this->contract_table}`.`contract_date`,
						   `{$this->contract_table}`.`rent_date_time`,
						   `{$this->contract_table}`.`return_date_time`,
						   `{$this->contract_table}`.`factual_return_date_time`,
						   `{$this->contract_table}`.`strating_milage`,
						   `{$this->contract_table}`.`return_milage`,
						   `{$this->contract_table}`.`price`,
						   `{$this->contract_table}`.`gas_amount_before_rent`,
						   `{$this->contract_table}`.`gar_amount_after_return`,
						   `{$this->contract_table}`.`state`,
						   `{$this->contract_table}`.`fk_CUSTOMERpasport_id`,
						   `{$this->contract_table}`.`fk_EMPLOYEEemploee_id`,
						   `{$this->contract_table}`.`fk_CARid_CAR`,
						   `{$this->contract_table}`.`fk_PARKING_LOTid_PARKING_LOT`,
						   `{$this->contract_table}`.`fk_PARKING_LOTid_PARKING_LOT1`,
						   (IFNULL(SUM(`{$this->selected_services_table}`.`price` * `{$this->selected_services_table}`.`amount`), 0) + `{$this->contract_table}`.`price`) AS `total_price`
					FROM `{$this->contract_table}`
						LEFT JOIN `{$this->selected_services_table}`
							ON `{$this->contract_table}`.`number`=`{$this->selected_services_table}`.`fk_CONTRACTnumber`
					WHERE `{$this->contract_table}`.`number`='{$nr}' GROUP BY `{$this->contract_table}`.`number`";
		$data = mysql::select($query);

		//
		return $data[0];
	}
	
	/**
	 * Check if the contract with designated number already exists
	 * @param type $nr
	 * @return type
	 */
	public function checkIfContractNrExists($nr) {
		$nr = mysql::escapeFieldForSQL($nr);

		$query = "  SELECT COUNT(`{$this->contract_table}`.`number`) AS `amount`
					FROM `{$this->contract_table}`
					WHERE `{$this->contract_table}`.`number`='{$nr}'";
		$data = mysql::select($query);

		//
		return $data[0]['amount'];
	}


    /**
     * Selection of selected aditional services
     * @param type $orderId
     * @return type
     */
    public function checkIfOrderedServiceExists($contractId, $serviceId, $priceFrom) {
        $query = "SELECT COUNT(`{$this->selected_services_table}`.`fk_CONTRACTnumber`) AS `amount`
				FROM `{$this->selected_services_table}`
				WHERE `fk_CONTRACTnumber`='{$contractId}' AND `fk_ID_SERVICE`='{$serviceId}' AND `fk_SERVICE_PRICEvalid_from`='{$priceFrom}'";
        $data = mysql::select($query);

        //
        return $data[0]['amount'];
    }

	/**
	 * Selection of extra ordered services list
	 * @param type $orderId
	 * @return type
	 */
	public function getOrderedServices($orderId) {
		$orderId = mysql::escapeFieldForSQL($orderId);


        $query = "SELECT `{$this->selected_services_table}`.`fk_CONTRACTnumber`,
					  `{$this->selected_services_table}`.`fk_SERVICE_PRICEvalid_from`,
					  `{$this->selected_services_table}`.`fk_ID_SERVICE`,
					  `{$this->selected_services_table}`.`amount`,
					  `{$this->selected_services_table}`.`price`,
					  `{$this->services_table}`.`name`
				FROM `{$this->selected_services_table}`
					LEFT JOIN `{$this->services_table}`
						ON `{$this->selected_services_table}`.`fk_ID_SERVICE`=`{$this->services_table}`.`id_SERVICE`
				WHERE `fk_CONTRACTnumber`='{$orderId}'";

		$data = mysql::select($query);

		return $data;
	}



	
	/**
	 * Contract update
	 * @param type $data
	 */
	public function updateContract($data) {
		$data = mysql::escapeFieldsArrayForSQL($data);

		$query = "  UPDATE `{$this->contract_table}`
					SET    `contract_date`='{$data['contract_date']}',
						   `rent_date_time`='{$data['rent_date_time']}',
						   `return_date_time`='{$data['return_date_time']}',
						   `factual_return_date_time`='{$data['factual_return_date_time']}',
						   `strating_milage`='{$data['strating_milage']}',
						   `return_milage`='{$data['return_milage']}',
						   `price`='{$data['price']}',
						   `gas_amount_before_rent`='{$data['gas_amount_before_rent']}',
						   `gar_amount_after_return`='{$data['gar_amount_after_return']}',
						   `state`='{$data['state']}',
						   `fk_CUSTOMERpasport_id`='{$data['fk_CUSTOMERpasport_id']}',
						   `fk_EMPLOYEEemploee_id`='{$data['fk_EMPLOYEEemploee_id']}',
						   `fk_CARid_CAR`='{$data['fk_CARid_CAR']}',
						   `fk_PARKING_LOTid_PARKING_LOT`='{$data['fk_PARKING_LOTid_PARKING_LOT']}',
						   `fk_PARKING_LOTid_PARKING_LOT1`='{$data['fk_PARKING_LOTid_PARKING_LOT1']}'
					WHERE `number`='{$data['number']}'";
		mysql::query($query);
	}


    public function updateOrderedService($data) {
        $data = mysql::escapeFieldsArrayForSQL($data);

        $query = "UPDATE `{$this->selected_services_table}`
				SET `price`='{$data['price']}',
				    `amount`='{$data['amount']}'
				WHERE `fk_CONTRACTnumber`='{$data['fk_CONTRACTnumber']}' AND `fk_SERVICE_PRICEvalid_from`='{$data['fk_SERVICE_PRICEvalid_from']}' AND `fk_ID_SERVICE`='{$data['fk_ID_SERVICE']}'";
        mysql::query($query);
    }
	
	/**
	 * Contract insertion
	 * @param type $data
	 */
	public function insertContract($data) {
		$data = mysql::escapeFieldsArrayForSQL($data);
		$query = "  INSERT INTO `{$this->contract_table}`
								(
									`number`,
									`contract_date`,
									`rent_date_time`,
									`return_date_time`,
									`factual_return_date_time`,
									`strating_milage`,
									`return_milage`,
									`price`,
									`gas_amount_before_rent`,
									`gar_amount_after_return`,
									`state`,
									`fk_CUSTOMERpasport_id`,
									`fk_EMPLOYEEemploee_id`,
									`fk_CARid_CAR`,
									`fk_PARKING_LOTid_PARKING_LOT`,
									`fk_PARKING_LOTid_PARKING_LOT1`
								)
								VALUES
								(
									'{$data['number']}',
									'{$data['contract_date']}',
									'{$data['rent_date_time']}',
									'{$data['return_date_time']}',
									'{$data['factual_return_date_time']}',
									'{$data['strating_milage']}',
									'{$data['return_milage']}',
									'{$data['price']}',
									'{$data['gas_amount_before_rent']}',
									'{$data['gar_amount_after_return']}',
									'{$data['state']}',
									'{$data['fk_CUSTOMERpasport_id']}',
									'{$data['fk_EMPLOYEEemploee_id']}',
									'{$data['fk_CARid_CAR']}',
									'{$data['fk_PARKING_LOTid_PARKING_LOT']}',
									'{$data['fk_PARKING_LOTid_PARKING_LOT1']}'
								)";
		mysql::query($query);
	}
	
	/**
	 * Contract removal
	 * @param type $id
	 */
	public function deleteContract($id) {
		$id = mysql::escapeFieldForSQL($id);

		$query = "  DELETE FROM `{$this->contract_table}`
					WHERE `number`='{$id}'";
		mysql::query($query);
	}
	
	/**
	 * Removal of all of the contract's ordered extra services
	 * @param type $contractId
	 */
	public function deleteOrderedServices($contractId) {
		$contractId = mysql::escapeFieldForSQL($contractId);

		$query = "  DELETE FROM `{$this->selected_services_table}`
					WHERE `fk_CONTRACTnumber`='{$contractId}'";
		mysql::query($query);
	}
	
	/**
	 * Removal of the contract's ordered extra service
	 * @param type $contractId
	 */
	public function deleteOrderedService($contractId, $serviceId, $priceFrom) {
		$contractId = mysql::escapeFieldForSQL($contractId);
		$serviceId = mysql::escapeFieldForSQL($serviceId);
		$priceFrom = mysql::escapeFieldForSQL($priceFrom);

		$query = "  DELETE FROM `{$this->selected_services_table}`
					WHERE `fk_CONTRACTnumber`='{$contractId}' AND `fk_ID_SERVICE`='{$serviceId}' AND `fk_SERVICE_PRICEvalid_from`='{$priceFrom}'";
		mysql::query($query);
	}

	/**
	 * Ordered extra service update
	 * @param type $data
	 */
	public function updateOrderedServices($data) {
		$data = mysql::escapeFieldsArrayForSQL($data);

		$this->deleteOrderedServices($data['number']);
		
		if(isset($data['services']) && sizeof($data['services']) > 0) {
			foreach($data['services'] as $key=>$val) {
				$tmp = explode("#", $val);
				$serviceId = $tmp[0];
				$priceFrom = $tmp[1];
				$price = $tmp[2];
				
				$query = "  INSERT INTO `{$this->selected_services_table}`
										(
											`fk_CONTRACTnumber`,
											`fk_SERVICE_PRICEvalid_from`,
											`Id_SELECTED_SERVICE`,
											`amount`,
											`price`
										)
										VALUES
										(
											'{$data['number']}',
											'{$priceFrom}',
											'{$serviceId}',
											'{$data['amount'][$key]}',
											'{$price}'
										)";
					mysql::query($query);
			}
		}
	}
	
	/**
	 * Ordered extra service inclusion
	 * @param type $data
	 */
	public function insertOrderedService($contractId, $priceFrom, $price, $amount, $fk_service) {
		$contractId = mysql::escapeFieldForSQL($contractId);
		$priceFrom = mysql::escapeFieldForSQL($priceFrom);
        $fk_service = mysql::escapeFieldForSQL($fk_service);
		$price = mysql::escapeFieldForSQL($price);
		$amount = mysql::escapeFieldForSQL($amount);
        $nextID = $this->lastSelectedServiceID()+1;

				$query = "  INSERT INTO `{$this->selected_services_table}`
										(
										    
											`Id_SELECTED_SERVICE`,
											`fk_CONTRACTnumber`,
											`fk_SERVICE_PRICEvalid_from`,
										    `fk_id_SERVICE`,
											`amount`,
											`price`
										)
										VALUES
										(
										 
											'{$nextID}',
											'{$contractId}',
											'{$priceFrom}',
										    '{$fk_service}',
											'{$amount}',
											'{$price}'
										)";
				mysql::query($query);
	}


	/**
	 * Contract state list selection
	 * @return type
	 */
	public function getContractStates() {
		$query = "  SELECT *
					FROM `{$this->contract_states_table}`";
		$data = mysql::select($query);
		
		//
		return $data;
	}
	
	/**
	 * Parking lot list selection
	 * @return type
	 */
	public function getParkingLots() {
		$query = "  SELECT *
					FROM `{$this->parking_lots_table}`";
		$data = mysql::select($query);
		
		//
		return $data;
	}
	
	/**
	 * Amount of service prices included in orders selection
	 * @param type $serviceId
	 * @param type $validFrom
	 * @return type
	 */
	public function getPricesCountOfOrderedServices($serviceId, $validFrom) {
		$serviceId = mysql::escapeFieldForSQL($serviceId);
		$validFrom = mysql::escapeFieldForSQL($validFrom);
		
		$query = "  SELECT COUNT(`{$this->selected_services_table}`.`Id_SELECTED_SERVICE`) AS `amount`
					FROM `{$this->service_prices_table}`
						INNER JOIN `{$this->selected_services_table}`
							ON `{$this->service_prices_table}`.`fk_SERVICEid_SERVICE`=`{$this->selected_services_table}`.`fk_ID_SERVICE` AND `{$this->service_prices_table}`.`valid_from`=`{$this->selected_services_table}`.`fk_SERVICE_PRICEvalid_from`
					WHERE `{$this->service_prices_table}`.`fk_SERVICEid_SERVICE`='{$serviceId}' AND `{$this->service_prices_table}`.`valid_from`='{$validFrom}'";
		$data = mysql::select($query);
		
		//
		return $data[0]['amount'];
	}

	public function getCustomerContracts($dateFrom, $dateTo) {
		$dateFrom = mysql::escapeFieldForSQL($dateFrom);
		$dateTo = mysql::escapeFieldForSQL($dateTo);

		$whereClauseString = "";
		if(!empty($dateFrom)) {
			$whereClauseString .= " WHERE `{$this->contract_table}`.`contract_date`>='{$dateFrom}'";
			if(!empty($dateTo)) {
				$whereClauseString .= " AND `{$this->contract_table}`.`contract_date`<='{$dateTo}'";
			}
		} else {
			if(!empty($dateTo)) {
				$whereClauseString .= " WHERE `{$this->contract_table}`.`contract_date`<='{$dateTo}'";
			}
		}
		
		$query = "  SELECT  `{$this->contract_table}`.`number`,
							`{$this->contract_table}`.`contract_date`,
							`{$this->customer_table}`.`pasport_id`,
							`{$this->customer_table}`.`name`,
						    `{$this->customer_table}`.`surname`,
						    `{$this->contract_table}`.`price` as `contract_price`,
						    IFNULL(sum(`{$this->selected_services_table}`.`amount` * `{$this->selected_services_table}`.`price`), 0) as `contract_service_price`,
						    `t`.`total_client_contact_price`,
						    `s`.`total_client_services_price`
					FROM `{$this->contract_table}`
						INNER JOIN `{$this->customer_table}`
							ON `{$this->contract_table}`.`fk_CUSTOMERpasport_id`=`{$this->customer_table}`.`pasport_id`
						LEFT JOIN `{$this->selected_services_table}`
							ON `{$this->contract_table}`.`number`=`{$this->selected_services_table}`.`fk_CONTRACTnumber`
						INNER JOIN (
							SELECT `pasport_id`,
									sum(`{$this->contract_table}`.`price`) AS `total_client_contact_price`
							FROM `{$this->contract_table}`
								INNER JOIN `{$this->customer_table}`
									ON `{$this->contract_table}`.`fk_CUSTOMERpasport_id`=`{$this->customer_table}`.`pasport_id`
							{$whereClauseString}
							GROUP BY `pasport_id`
						) `t` ON `t`.`pasport_id`=`{$this->customer_table}`.`pasport_id`
						INNER JOIN (
							SELECT `pasport_id`,
									IFNULL(sum(`{$this->selected_services_table}`.`amount` * `{$this->selected_services_table}`.`price`), 0) as `total_client_services_price`
							FROM `{$this->contract_table}`
								INNER JOIN `{$this->customer_table}`
									ON `{$this->contract_table}`.`fk_CUSTOMERpasport_id`=`{$this->customer_table}`.`pasport_id`
								LEFT JOIN `{$this->selected_services_table}`
									ON `{$this->contract_table}`.`number`=`{$this->selected_services_table}`.`fk_CONTRACTnumber`
								{$whereClauseString}							
								GROUP BY `pasport_id`
						) `s` ON `s`.`pasport_id`=`{$this->customer_table}`.`pasport_id`
					{$whereClauseString}
					GROUP BY `{$this->contract_table}`.`number` ORDER BY `{$this->customer_table}`.`surname` ASC";
		$data = mysql::select($query);

		//
		return $data;
	}
	
	public function getSumPriceOfContracts($dateFrom, $dateTo) {
		$dateFrom = mysql::escapeFieldForSQL($dateFrom);
		$dateTo = mysql::escapeFieldForSQL($dateTo);

		$whereClauseString = "";
		if(!empty($dateFrom)) {
			$whereClauseString .= " WHERE `{$this->contract_table}`.`contract_date`>='{$dateFrom}'";
			if(!empty($dateTo)) {
				$whereClauseString .= " AND `{$this->contract_table}`.`contract_date`<='{$dateTo}'";
			}
		} else {
			if(!empty($dateTo)) {
				$whereClauseString .= " WHERE `{$this->contract_table}`.`contract_date`<='{$dateTo}'";
			}
		}
		
		$query = "  SELECT sum(`{$this->contract_table}`.`price`) AS `rent_sum`
					FROM `{$this->contract_table}`
					{$whereClauseString}";
		$data = mysql::select($query);

		//
		return $data;
	}

	public function getSumPriceOfOrderedServices($dateFrom, $dateTo) {
		$dateFrom = mysql::escapeFieldForSQL($dateFrom);
		$dateTo = mysql::escapeFieldForSQL($dateTo);

		$whereClauseString = "";
		if(!empty($dateFrom)) {
			$whereClauseString .= " WHERE `{$this->contract_table}`.`contract_date`>='{$dateFrom}'";
			if(!empty($dateTo)) {
				$whereClauseString .= " AND `{$this->contract_table}`.`contract_date`<='{$dateTo}'";
			}
		} else {
			if(!empty($dateTo)) {
				$whereClauseString .= " WHERE `{$this->contract_table}`.`contract_date`<='{$dateTo}'";
			}
		}
		
		$query = "  SELECT sum(`{$this->selected_services_table}`.`amount` * `{$this->selected_services_table}`.`price`) AS `services_sum`
					FROM `{$this->contract_table}`
						INNER JOIN `{$this->selected_services_table}`
							ON `{$this->contract_table}`.`number`=`{$this->selected_services_table}`.`fk_CONTRACTnumber`
					{$whereClauseString}";
		$data = mysql::select($query);

		//
		return $data;
	}
	
	public function getDelayedCars($dateFrom, $dateTo) {
		$dateFrom = mysql::escapeFieldForSQL($dateFrom);
		$dateTo = mysql::escapeFieldForSQL($dateTo);

		$whereClauseString = "";
		if(!empty($dateFrom)) {
			$whereClauseString .= " AND `{$this->contract_table}`.`contract_date`>='{$dateFrom}'";
			if(!empty($dateTo)) {
				$whereClauseString .= " AND `{$this->contract_table}`.`contract_date`<='{$dateTo}'";
			}
		} else {
			if(!empty($dateTo)) {
				$whereClauseString .= " AND `{$this->contract_table}`.`contract_date`<='{$dateTo}'";
			}
		}
		
		$query = "  SELECT `number`,
						   `contract_date`,
						   `return_date_time`,
						   IF(`factual_return_date_time`='0000-00-00 00:00:00', 'not returned', `factual_return_date_time`) AS `returned`,
						   `{$this->customer_table}`.`name`,
						   `{$this->customer_table}`.`surname`
					FROM `{$this->contract_table}`
						INNER JOIN `{$this->customer_table}`
							ON `{$this->contract_table}`.`fk_CUSTOMERpasport_id`=`{$this->customer_table}`.`pasport_id`
					WHERE (DATEDIFF(`factual_return_date_time`, `return_date_time`) >= 1 OR
						(`factual_return_date_time` = '0000-00-00 00:00:00' AND DATEDIFF(NOW(), `return_date_time`) >= 1))
					{$whereClauseString}";
		$data = mysql::select($query);

		//
		return $data;
	}
	
}