<?php
class Bitpagos_Bitpagos_Block_Info_Mycustom extends Mage_Payment_Block_Info {
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $info = $this->getInfo();
        $transport = new Varien_Object();
        $transport = parent::_prepareSpecificInformation($transport);
        return $transport;
    }
}
?>