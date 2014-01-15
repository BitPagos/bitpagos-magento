<?php
class Bitpagos_Bitpagos_StandardController extends Mage_Core_Controller_Front_Action {

    protected $_order;    

    public function cancelAction( $error_mess ) {}

    public function  successAction() {        

        $store_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        
        $this->loadLayout();
        $block = $this->getLayout()->getBlock('bitpagos.btn');
        
        $block->setAccountId( Mage::getStoreConfig('payment/bitpagos/account_id', Mage::app()->getStore()) );
        
        $api_key = filter_var( Mage::getStoreConfig('payment/bitpagos/api_key'), FILTER_SANITIZE_STRING );
        $block->setApiKey( $api_key );
        
        $block->setIpnUrl( $store_url . 'index.php/bitpagos/standard/ipn/' );
        $block->setActionForm( $store_url . 'index.php/bitpagos/standard/post/' );        

        $reference_id = (int)Mage::getSingleton('checkout/session')->getLastOrderId();
        $block->setReferenceId( $reference_id );
        $order = Mage::getModel('sales/order')->load( $reference_id );
        $block->setOrderId( $reference_id );

        $block->setAmount( $order->getGrandTotal() );
        $block->setCurrency( 'USD' );        
        $block->setCanViewOrder( true );
        
        Mage::log( ' Success action: Order ' . $reference_id, null, 'bitpagos.log' );

        $this->renderLayout();

    }


    public function postAction() {

        if ( sizeOf( $_POST ) == 0 ) { 
            header("HTTP/1.1 500 EMPTY_POST ");
            return false;
        }

        if (!isset( $_POST['transactionId'] ) || 
            !isset( $_POST['referenceId'] ) ) {
            header("HTTP/1.1 500 BAD_PARAMETERS");
            return false;
        }

        $reference_id = (int)$_POST['referenceId'];
        $transaction_id = filter_var( $_POST['transactionId'], FILTER_SANITIZE_STRING);
        $this->changeOrderStatus( $reference_id, $transaction_id );

        Mage::log( ' Post action: Order ' . $reference_id, null, 'bitpagos.log' );

        $this->loadLayout();
        $this->getLayout()->getBlock('bitpagos.post')->setCanViewOrder( true );
        $this->renderLayout();

    }

    public function ipnAction() {

        if ( sizeOf( $_POST ) == 0 ) { 
            header("HTTP/1.1 500 EMPTY_POST ");
            return false;
        }

        if (!isset( $_POST['transaction_id'] ) || 
            !isset( $_POST['reference_id'] ) ) {
            header("HTTP/1.1 500 BAD_PARAMETERS");
            return false;
        }

        $reference_id = (int)$_POST['reference_id'];

        Mage::log( ' IPN action: Order ' . $reference_id, null, 'bitpagos.log' );

        $transaction_id = filter_var( $_POST['transaction_id'], FILTER_SANITIZE_STRING);
        $this->changeOrderStatus( $reference_id, $transaction_id );

    }

    private function changeOrderStatus( $reference_id, $transaction_id ) {

        $api_key = filter_var( Mage::getStoreConfig('payment/bitpagos/api_key'), FILTER_SANITIZE_STRING );
        
        // Checkout to bitpagos api
        $url = 'https://www.bitpagos.net/api/v1/transaction/' . $transaction_id . '/?api_key=' . $api_key . '&format=json';

        $cbp = curl_init( $url );
        curl_setopt($cbp, CURLOPT_RETURNTRANSFER, TRUE);
        $response_curl = curl_exec( $cbp );
        curl_close( $cbp );
        $response = json_decode( $response_curl );

        if ( $reference_id != $response->reference_id ) {
            header("HTTP/1.1 500 BAD_REFERENCE_ID");
            return false;
        }

        if ( $response->status == 'PA' || $response->status == 'CO' ) {

            Mage::log( ' Change order action: Order ' . $reference_id . ' Completed', null, 'bitpagos.log' );

            $order = Mage::getModel('sales/order')->load($reference_id);
            $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_COMPLETE);
            $order->save();

        }

    }

}
?>