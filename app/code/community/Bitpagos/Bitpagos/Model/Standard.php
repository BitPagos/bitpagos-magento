<?php

class Bitpagos_Bitpagos_Model_Standard extends Mage_Payment_Model_Method_Abstract {

    protected $_code = 'bitpagos';
 
    public function getOrderPlaceRedirectUrl() {        
        return Mage::getUrl('bitpagos/standard/success');
    }
    
    public function assignData($data) {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        return $this;
    }

    public function validate() {
        return $this;
    }

    /*
    public function validate() {
        
        // Use to validate bitpagos, if needed

        parent::validate();
        $info = $this->getInfoInstance();

        $no = $info->getCheckNo();
        $date = $info->getCheckDate();
        if( empty( $no ) ) {
            $errorCode = 'invalid_data';
            $errorMsg = $this->_getHelper()->__('Check No and Date are required fields');
        }

        if($errorMsg){
            Mage::throwException($errorMsg);
        }
        return $this;
    }*/

}
?>