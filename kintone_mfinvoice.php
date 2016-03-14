<?php
class CompanyClass extends CurlClass {
	function gets(){
		$url = "https://invoice.moneyforward.com/api/v1/partners.json?page=1&per_page=100";
		$type = 'GET';
		$result = CurlClass::curls($url,$type);
		if($result['meta']['total_pages'] > 1){
			for($i = 2; $i <= $result['meta']['total_pages']; $i++) {
				$url = "https://invoice.moneyforward.com/api/v1/partners.json?page=".$i."&per_page=100";
				$page_result = CurlClass::curls($url,$type);
				$result['partners'] = array_merge($result['partners'], $page_result['partners']);
			}
		}
		return $result['partners'];
	}
	function get($id = ""){
		$url = "https://invoice.moneyforward.com/api/v1/partners/".$id.".json";
		$type = 'GET';
		$result = CurlClass::curls($url,$type);
		return $result;
	}
	function add($value){
		$url = "https://invoice.moneyforward.com/api/v1/partners";
		$type = 'POST';
		$address = self::match_address($value['住所']['value']);
		$prefecture = array_shift($address);
		$address = implode('',$address);
		$data = array('partner' => array(
        	"code" => $value['数値']['value'],
	        'name' => $value['会社名']['value'],
    	    	"name_suffix" => "御中",
        	"zip" => $value['郵便番号']['value'],
       		"tel" => $value['電話番号']['value'],
        	'prefecture' => $prefecture, 
        	"address1" => $address,
        	"person_name" => ($value['経理担当者名']['value'] == "") ? '経理ご担当者' : $value['経理担当者名']['value'],
        	"email" => $value['メールアドレス']['value']
    	));
		$result = CurlClass::curls($url,$type,$data);
		return $result;
	}
	function edit($id = "", $department_id = "", $value = array()){
		$url = "https://invoice.moneyforward.com/api/v1/partners/".$id;
		$type = 'PATCH';
		$address = self::match_address($value['住所']['value']);
		$prefecture = array_shift($address);
		$address = implode('',$address);
		$data = array(
			'partner' => array(
        		"code" => $value['数値']['value'],
        		"name" => $value['会社名']['value'],
 		     	"name_suffix" => "御中",
			),
			'department'=> array(array(
				'id' => $department_id,
		    	"zip" => $value['郵便番号']['value'],
				"tel" => $value['電話番号']['value'],
				"prefecture" => $prefecture, 
				"address1" => $address,
				"person_name" => ($value['経理担当者名']['value'] == "") ? '経理ご担当者' : $value['経理担当者名']['value'],
				"email" => $value['メールアドレス']['value']
			))
		);
		$result = CurlClass::curls($url,$type,$data);
		return $result;
	}
	function match_address($str){
		preg_match("/^.+(都|北海道|県|府)/",$str,$matches);
	    if(empty($matches[0])){
    		preg_match("/^.+(都|道)/",$str,$matches);
    	}   
		$matches[1] = mb_substr($str, mb_strlen($matches[0]));
		if($matches[1] == ""){
			$matches[1] = $matches[0];
			$matches[0] = "-";
		}
		return $matches;
	}
}

class BillingClass extends CurlClass {
	function gets(){
		$url = "https://invoice.moneyforward.com/api/v1/billings.json?page=1&per_page=100";
		$type = 'GET';
		$result = CurlClass::curls($url,$type);
		if($result['meta']['total_pages'] > 1){
			for($i = 2; $i <= $result['meta']['total_pages']; $i++) {
				$url = "https://invoice.moneyforward.com/api/v1/billings.json?page=".$i."&per_page=100";
				$page_result = CurlClass::curls($url,$type);
				$result['billings'] = array_merge($result['billings'], $page_result['billings']);
			}
		}
		return $result['billings'];
	}
	function get($id = ""){
		$url = "https://invoice.moneyforward.com/api/v1/billings/".$id.".json";
		$type = 'GET';
		$result = CurlClass::curls($url,$type);
		return $result;
	}
	function add($department_id,$value){
		$url = "https://invoice.moneyforward.com/api/v1/billings";
		$type = 'POST';
		$title = "サービス利用料";
		$note = "恐れ入りますが、振込手数料はお客様負担でお願いいたします。";
		$document_name = "請求書";
		$payment_condition = "XX銀行XXX支店\n普通預金XXXXXXXX\nXXXXXXXXXXX";
		$data = array(
			'billing' => array(
				"department_id" => $department_id,
				"title" => $title,
				"payment_condition" => $payment_condition,
				"note" => $note,
				"billing_date" => $value['請求日']['value'],
				"due_date" => $value['支払期限']['value'],
				"sales_date" => $value['売上計上日']['value'],
				"document_name" => $document_name,
				"tags" => $value['タグ']['value'],
				"items" => array(
					array(
						"code" => $value['レコード番号']['value'], 
					),
				)
			)
		);
		$result = CurlClass::curls($url,$type,$data);
		return $result;
	}
	function edit($id = "", $billings = array(), $value = array()){
		$url = "https://invoice.moneyforward.com/api/v1/billings";
		$type = 'POST';
		$title = "サービス利用料";
		$note = "恐れ入りますが、振込手数料はお客様負担でお願いいたします。";
		$document_name = "請求書";
		$payment_condition = "XX銀行XXX支店\n普通預金XXXXXXXX\nXXXXXXXXXXX";
		$data = array(
			'billing' => array(
				"department_id" => $billings['department_id'],
				"title" => $title,
				"payment_condition" => $payment_condition,
				"note" => $note,
				"billing_date" => $value['請求日']['value'],
				"due_date" => $value['支払期限']['value'],
				"sales_date" => $value['売上計上日']['value'],
				"document_name" => $document_name,
				"tags" => $value['タグ']['value']
			)
		);
		$item_ids = array();
		foreach ($billings["items"] as $key => $item) {
			$data["billing"]["items"][$key]['code'] = $item['code'];
			$item_ids[] = $item['code'];
		}
		if(!in_array($value['レコード番号']['value'],$item_ids)){
			$data["billing"]["items"][]['code'] = $value['レコード番号']['value'];
			self::del($billings["id"]);
		}else{
			$url = "https://invoice.moneyforward.com/api/v1/billings/" . $id;
			$type = 'PATCH';
		}
		$result = CurlClass::curls($url,$type,$data);
		return $result;
	}
	function search($q="",$range_key="",$from="",$to=""){
		if($q && $range_key){
			$q = urlencode($q)."&";
		}
		$range ="";
		if($range_key && $from && $to){
			$range = "range_key=" . $range_key . "&from=" . $from . "&to=" . $to;
		}
		$url = "https://invoice.moneyforward.com/api/v1/billings/search.json?".$q.$range;
		$type = 'GET';
		$result = CurlClass::curls($url,$type);
		return $result['billings'];
	}
	function del($id = ""){
		$url = "https://invoice.moneyforward.com/api/v1/billings/" . $id;
		$type = 'DELETE';
		$result = CurlClass::curls($url,$type);
		return $result;
	}
}

class ItemClass extends CurlClass {
	function gets(){
		$url = "https://invoice.moneyforward.com/api/v1/items.json?page=1&per_page=100";
		$type = 'GET';
		$result = CurlClass::curls($url,$type);
		return $result['items'];
	}
	function add($value){
		$url = "https://invoice.moneyforward.com/api/v1/items";
		$type = 'POST';
		$data = array('item' => array(
	        'name' => $value['案件名']['value'],
        	"code" => $value['レコード番号']['value'],
			"detail" => $value['詳細']['value'],
			"unit_price" => $value['小計']['value'],
			"quantity" => "1",
			"unit" => "式",
			"excise" => true
    	));
		$result = CurlClass::curls($url,$type,$data);
		return $result;
	}
	function edit($id = "", $value = array()){
		$url = "https://invoice.moneyforward.com/api/v1/items/".$id;
		$type = 'PATCH';
		$data = array('item' => array(
	        'name' => $value['案件名']['value'],
        	"code" => $value['レコード番号']['value'],
			"detail" => $value['詳細']['value'],
			"unit_price" => $value['小計']['value'],
			"quantity" => "1",
			"unit" => "式",
			"excise" => true
    	));
		$result = CurlClass::curls($url,$type,$data);
		return $result;
	}
}

class CurlClass {
	function curls($url,$type,$data = array()){
		$time_out = 30;
		$ch = curl_init();
		// 保存するファイル
		$fp = fopen('./curl.log', 'a');
		// 詳細な情報を出力する
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		// STDERR の代わりにエラーを出力するファイルポインタ
		curl_setopt($ch, CURLOPT_STDERR, $fp);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $time_out);
		curl_setopt($ch, CURLOPT_TIMEOUT, $time_out);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);

		switch ($type) {
		    case 'GET':
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/json',
					'Authorization: BEARER XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX' //Authorizationを入力
				));
		        break;
		    case 'POST':
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/json',
					'Authorization: BEARER XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX' //Authorizationを入力
				));
				curl_setopt($ch, CURLOPT_POST, TRUE); 
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		        break;
		    case 'PATCH':
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/json',
					'Authorization: BEARER XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX' //Authorizationを入力
				));
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		        break;
		    default:
		    	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/json',
					'Authorization: BEARER XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX' //Authorizationを入力
				));
		        break;
		}
		
		$response = curl_exec($ch);
		// json形式で返ってくるので、配列に変換
		$result = json_decode($response, true);

		fclose($fp);
		curl_close($ch);
		return $result;
	}
}

function get_vals($datas = array(),$val = "name"){
	$vals = array();
	foreach ($datas as $key => $data) {
		$vals[] = $data[$val];
	}
	return $vals;
}



/* メインロジックここから */
header('Access-Control-Allow-Origin:*');
header('Content-Type: application/json; charset=UTF-8');

$data = (isset($_POST["resp"]) ? $_POST["resp"] : array());

$Company = new CompanyClass();
$Billing = new BillingClass();
$Item = new ItemClass();

$conmanies = $Company->gets();
$conmany_ids = get_vals($conmanies,'code');
$items = $Item->gets();
$item_ids = get_vals($items,'code');
$result = array();


foreach ($data as $key => $value) {
	if(in_array($value['数値']['value'],$conmany_ids)){
		$conmany_key = array_search($value['数値']['value'],$conmany_ids);
		//取引先更新
		$conmany = $Company->edit($conmanies[$conmany_key]['id'], $conmanies[$conmany_key]['departments'][0]['id'], $value);
	}else{
		//取引先登録
		$conmany = $Company->add($value);
		$conmanies[] = $conmany;
		$conmany_ids[] = $conmany['code'];
	}

	//一致確認
	if(in_array($value['レコード番号']['value'],$item_ids)){
		$item_key = array_search($value['レコード番号']['value'],$item_ids);
		$item = $Item->edit($items[$item_key]['id'], $value);
	}else{
		$item = $Item->add($value);
		$items[] = $item;
		$item_ids[] = $item['code'];
	}

	//due_date 支払期限
	$is_billing = false;
	$billings = $Billing->search($q="",$range_key="due_date",$from=$value['支払期限']['value'],$to=$value['支払期限']['value']);
	foreach ($billings as $key => $billing) {
		if($billing['partner_name'] == $value['会社名']['value']){
			$is_billing = true;
			$is_billing_key = $key;
		}
	}
	if($is_billing){
		//案件更新(追加あり)
		$billing = $Billing->edit($billings[$is_billing_key]['id'], $billings[$is_billing_key], $value);
	}else{
		$billing = $Billing->add($conmany['departments'][0]['id'],$value);
	}
	if($billing){
		$result[] = $billing;
	}
}
echo json_encode(count($result));
?>
