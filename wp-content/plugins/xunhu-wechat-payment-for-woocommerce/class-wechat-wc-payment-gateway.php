<?php
if (! defined ( 'ABSPATH' ))
	exit (); // Exit if accessed directly
class XH_Wechat_Payment_WC_Payment_Gateway extends WC_Payment_Gateway {
    private $instructions;
	public function __construct() {
		$this->id                 = XH_Wechat_Payment_ID;
		$this->icon               = XH_Wechat_Payment_URL . '/images/logo/wechat.png';
		$this->has_fields         = false;
		
		$this->method_title       = __('Wechat Payment',XH_Wechat_Payment);
		$this->method_description = __('Helps to add Wechat payment gateway that supports the features including QR code payment, OA native payment, exchange rate.',XH_Wechat_Payment);
		
		$this->title              = $this->get_option ( 'title' );
		$this->description        = $this->get_option ( 'description' );
		
		$this->enabled            = $this->get_option ( 'enabled' );
		$this->instructions       = $this->get_option('instructions');
		
		$this->init_form_fields ();
		$this->init_settings ();
		
		
		add_filter ( 'woocommerce_payment_gateways', array($this,'woocommerce_add_gateway') );
		add_action ( 'woocommerce_update_options_payment_gateways_' .$this->id, array ($this,'process_admin_options') );
		add_action ( 'woocommerce_update_options_payment_gateways', array ($this,'process_admin_options') );
		add_action ( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
		add_action ( 'woocommerce_thankyou_'.$this->id, array( $this, 'thankyou_page' ) );
	}
	
	public function notify(){
	    global $XH_Wechat_Payment_WC_Payment_Gateway;
	     
	    $data = $_POST;
	    if(!isset($data['hash'])
	        ||!isset($data['trade_order_id'])){
	            return;
	    }
	    if(isset($data['plugins'])&&$data['plugins']!='woo-wechat'){
	        return;
	    }
	
	    $appkey =$XH_Wechat_Payment_WC_Payment_Gateway->get_option('appsecret');
	    $hash =$XH_Wechat_Payment_WC_Payment_Gateway->generate_xh_hash($data,$appkey);
	    if($data['hash']!=$hash){
	        return;
	    }
	
	    $order = wc_get_order($data['trade_order_id']);
	    try{
	        if(!$order){
	            throw new Exception('Unknow Order (id:'.$data['trade_order_id'].')');
	        }
	
	        if(!(method_exists($order, 'is_paid')?$order->is_paid():in_array($order->get_status(),  array( 'processing', 'completed' )))&&$data['status']=='OD'){
	            $order->payment_complete(isset($data['transacton_id'])?$data['transacton_id']:'');
	        }
	    }catch(Exception $e){
	        //looger
	        $logger = new WC_Logger();
	        $logger->add( 'xh_wedchat_payment', $e->getMessage() );
	
	        $params = array(
	            'action'=>'fail',
	            'appid'=>$XH_Wechat_Payment_WC_Payment_Gateway->get_option('appid'),
	            'errcode'=>$e->getCode(),
	            'errmsg'=>$e->getMessage()
	        );
	
	        $params['hash']=$XH_Wechat_Payment_WC_Payment_Gateway->generate_xh_hash($params, $appkey);
	        ob_clean();
	        print json_encode($params);
	        exit;
	    }
	
	    $params = array(
	        'action'=>'success',
	        'appid'=>$XH_Wechat_Payment_WC_Payment_Gateway->get_option('appid')
	    );
	
	    $params['hash']=$XH_Wechat_Payment_WC_Payment_Gateway->generate_xh_hash($params, $appkey);
	    ob_clean();
	    print json_encode($params);
	    exit;
	}
	public function woocommerce_add_gateway($methods) {
	    $methods [] = $this;
	    return $methods;
	}
	
	public function process_payment($order_id) {
		$order            = wc_get_order ( $order_id );
		if(!$order||(method_exists($order, 'is_paid')?$order->is_paid():in_array($order->get_status(),  array( 'processing', 'completed' )))){
		    return array (
		        'result' => 'success',
		        'redirect' => $this->get_return_url($order)
		    );
		}
		
		$expire_rate      = floatval($this->get_option('exchange_rate',1));
		if($expire_rate<=0){
		    $expire_rate=1;
		}
		
		$siteurl = rtrim(home_url(),'/');
		$posi =strripos($siteurl, '/');
		//若是二级目录域名，需要以“/”结尾，否则会出现403跳转
		if($posi!==false&&$posi>7){
		    $siteurl.='/';
		}
		
		$total_amount     = round($order->get_total()*$expire_rate,2);		
		$data=array(
		      'version'   => '1.1',//api version
		      'lang'       => get_option('WPLANG','zh-cn'),   
		      'plugins'   => 'woo-wechat',
		      'appid'     => $this->get_option('appid'),
		      'trade_order_id'=> $order_id,
		      'payment'   => 'wechat',
		      'is_app'    => $this->is_wechat_app()?'Y':'N',
		      'total_fee' => $total_amount,
		      'title'     => $this->get_order_title($order),
		      'description'=> null,
		      'time'      => time(),
		      'notify_url'=>  $siteurl,
		      'return_url'=> $this->get_return_url($order),
		      'callback_url'=>wc_get_checkout_url(),
		      'nonce_str' => str_shuffle(time())
		);
		
		$hashkey          = $this->get_option('appsecret');
		$data['hash']     = $this->generate_xh_hash($data,$hashkey);
		$url              = rtrim($this->get_option('tranasction_url')).'/payment/do.html';
		
		try {
		    $response     = $this->http_post($url, json_encode($data));
		    $result       = $response?json_decode($response,true):null;
		    if(!$result){
		        throw new Exception('Internal server error',500);
		    }
		     
		    $hash         = $this->generate_xh_hash($result,$hashkey);
		    if(!isset( $result['hash'])|| $hash!=$result['hash']){
		        throw new Exception(__('Invalid sign!',XH_Wechat_Payment),40029);
		    }
		    
		    if($result['errcode']!=0){
		        throw new Exception($result['errmsg'],$result['errcode']);
		    }
		    
		    return array(
		        'result'  => 'success',
		        'redirect'=> $result['url']
		    );
		} catch (Exception $e) {
		    wc_add_notice("errcode:{$e->getCode()},errmsg:{$e->getMessage()}",'error');
		    return array(
		        'result' => 'fail',
		        'redirect' => $this->get_return_url($order)
		    );
		}
	}
	
	private function http_post($url,$data){
	    if(!function_exists('curl_init')){
	        throw new Exception('php未安装curl组件',500);
	    }
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	    curl_setopt($ch,CURLOPT_URL, $url);
	    curl_setopt($ch,CURLOPT_REFERER,get_option('siteurl'));
	    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
	    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
	    curl_setopt($ch, CURLOPT_HEADER, FALSE);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    curl_setopt($ch, CURLOPT_POST, TRUE);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	    $response = curl_exec($ch);
	    $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	    $error=curl_error($ch);
	    curl_close($ch);
	    if($httpStatusCode!=200){
	        throw new Exception("invalid httpstatus:{$httpStatusCode} ,response:$response,detail_error:".$error,$httpStatusCode);
	    }
	    
	    return $response;
	}
	
	public function generate_xh_hash(array $datas,$hashkey){
	    ksort($datas);
	    reset($datas);
	    
	    $pre =array();
	    foreach ($datas as $key => $data){
	        if(is_null($data)||$data===''){continue;}
	        if($key=='hash'){
	            continue;
	        }
	        $pre[$key]=$data;
	    }
	    
	    $arg  = '';
	    $qty = count($pre);
	    $index=0;
	    
	    foreach ($pre as $key=>$val){
	        $arg.="$key=$val";
	        if($index++<($qty-1)){
	            $arg.="&";
	        }
	    }
	    
	    return md5($arg.$hashkey);
	}
	
	private function is_wechat_app(){
	    return strripos($_SERVER['HTTP_USER_AGENT'],'micromessenger');
	}
	
	public function thankyou_page() {
	    if ( $this->instructions ) {
	        echo wpautop( wptexturize( $this->instructions ) );
	    }
	}
	
	/**
	 * Add content to the WC emails.
	 *
	 * @access public
	 * @param WC_Order $order
	 * @param bool $sent_to_admin
	 * @param bool $plain_text
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
	    $method = method_exists($order ,'get_payment_method')?$order->get_payment_method():$order->payment_method;
	    if ( $this->instructions && ! $sent_to_admin && $this->id ===$method) {
	        echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
	    }
	}
	/**
	 * Initialise Gateway Settings Form Fields
	 *
	 * @access public
	 * @return void
	 */
	function init_form_fields() {
		$this->form_fields = array (
				'enabled' => array (
						'title'       => __('Enable/Disable',XH_Wechat_Payment),
						'type'        => 'checkbox',
						'label'       => __('Enable/Disable the wechat payment',XH_Wechat_Payment),
						'default'     => 'no',
						'section'     => 'default'
				),
				'title' => array (
						'title'       => __('Payment gateway title',XH_Wechat_Payment),
						'type'        => 'text',
						'default'     =>  __('Wechat Payment',XH_Wechat_Payment),
						'desc_tip'    => true,
						'css'         => 'width:400px',
						'section'     => 'default'
				),
				'description' => array (
						'title'       => __('Payment gateway description',XH_Wechat_Payment),
						'type'        => 'textarea',
						'default'     => __('QR code payment or OA native payment, credit card',XH_Wechat_Payment),
						'desc_tip'    => true,
						'css'         => 'width:400px',
						'section'     => 'default'
				),
				'instructions' => array(
    					'title'       => __( 'Instructions', XH_Wechat_Payment ),
    					'type'        => 'textarea',
    					'css'         => 'width:400px',
    					'description' => __( 'Instructions that will be added to the thank you page.', XH_Wechat_Payment ),
    					'default'     => '',
    					'section'     => 'default'
				),
				'appid' => array(
    					'title'       => __( 'APP ID', XH_Wechat_Payment ),
    					'type'        => 'text',
    					'css'         => 'width:400px',
    					 'default'=>'20146122711',
    					'section'     => 'default',
                        'description' =>'WP开放平台 <a href="http://mp.wordpressopen.com" target="_blank">注册创建应用获取Appid</a>'
				),
				'appsecret' => array(
    					'title'       => __( 'APP Secret', XH_Wechat_Payment ),
    					'type'        => 'text',
    					'css'         => 'width:400px',
    					 'default'=>'44E76C565F233E4CBB4F5E1B26E2D2A1',
    					'section'     => 'default'
				),
				'tranasction_url' => array(
    					'title'       => __( 'Transaction_url', XH_Wechat_Payment ),
    					'type'        => 'text',
    					'css'         => 'width:400px',
    					 'default'=>'https://pay.wordpressopen.com',
    					'section'     => 'default',
				    'description'=>''
				),
				'exchange_rate' => array (
    					'title'       => __( 'Exchange Rate',XH_Wechat_Payment),
    					'type'        => 'text',
    					'default'     => '1',
    					'description' => __(  'Set the exchange rate to RMB. When it is RMB, the default is 1',XH_Wechat_Payment),
    					'css'         => 'width:400px;',
    					'section'     => 'default'
				)
		);
	}
	   
	public function get_order_title($order, $limit = 98) {
	    $order_id = method_exists($order, 'get_id')? $order->get_id():$order->id;
		$title ="#{$order_id}";
		
		$order_items = $order->get_items();
		if($order_items){
		    $qty = count($order_items);
		    foreach ($order_items as $item_id =>$item){
		        $title.="|{$item['name']}";
		        break;
		    }
		    if($qty>1){
		        $title.='...';
		    }
		}
		
		$title = mb_strimwidth($title, 0, $limit,'utf-8');
		return apply_filters('xh-payment-get-order-title', $title,$order);
	}
	
}

?>
