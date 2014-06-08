<?php

/**
 * Class RobokassaController
 * Controller callbacks for Robokassa API
 *
 * @author Maksym Bugai <marvelsrp@gmail.com>
 */
class RobokassaController extends CController{

	private $_login;
	private $_password1;
	private $_password2;
	
	/**
	* Result
	*/
    public function actionResult(){
	
        $out_sum = Yii::app()->request->getPost("OutSum");
        $inv_id = Yii::app()->request->getPost("InvId");
        $pay_key = Yii::app()->request->getPost("Shp_payKey");
        $response_crc = Yii::app()->request->getPost("SignatureValue");
        $response_crc = strtoupper($response_crc);

        $valid = self::validSignature($out_sum,$inv_id,$pay_key,$response_crc);

        if (!$valid){
            echo "bad sign\n";
            Yii::app()->end();
        }

        echo "OK".$inv_id;
    }
	
	/**
	* Success result
	*/
    public function actionSuccess(){

        $pay_key = Yii::app()->request->getPost("Shp_payKey");
        $out_sum = Yii::app()->request->getPost("OutSum");
        $inv_id = Yii::app()->request->getPost("InvId");
        $response_crc = Yii::app()->request->getPost("SignatureValue");


		$login = "login";
		$pass2 = "password";

		if (empty($login) || empty($pass2)){
			echo self::ERROR_AUTH;
			Yii::app()->end();
		}

		$paymentStatus = self::validSignature($out_sum,$inv_id,$pay_key,$response_crc);
		if (!$paymentStatus) {
			echo self::ERROR_SIGNATURE;
			Yii::app()->end();
		}
		//success
		

    }
	
	/**
	* Fail result
	*/
    public function actionFail(){
	
		//fail
    }
	
	/**
	* Validate Signature
	* @param $out_sum
	* @param $inv_id
	* @param $pay_key
	* @param $response_crc
	*/
	static public function validSignature($out_sum,$inv_id,$pay_key,$response_crc){
	
        $my_crc = strtoupper(md5("$out_sum:$inv_id:$this->_password2:Shp_payKey=$pay_key"));
            if ($my_crc == $response_crc) {
            return true;
        } else {
            return false;
        }
    }
	/**
	* Generate url redirect to Robokassa
	* @param $num string Number of order
	*/
	static public function generateURL($num){
		
        $inv_desc = "Pay № ".$num;
        $out_sum = $order->all_price;
        $in_curr = "";
        $culture = "ru";
        $crc = md5("$login:$out_sum:$num:$this->_password1:Shp_payKey=$num");

        $url = 'http://test.robokassa.ru/Index.aspx';
        //$url = 'https://merchant.roboxchange.com/Index.aspx';

        $params = array(
            "MrchLogin=$this->_login",
            "OutSum=$out_sum",
            "InvId=$num",
            "Desc=$inv_desc",
            "SignatureValue=$crc",
            "Shp_payKey=$num",
            "IncCurrLabel=$in_curr",
            "Culture=$culture",
        );
        $url = $url . "?" . implode("&", $params);

        return $url;
    }

}