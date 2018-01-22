<?php
/**
 * Copyright (c) 2015, Marcel Hauri
 * All rights reserved.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @copyright Copyright 2015, Marcel Hauri (https://github.com/mhauri/magento-slack/)
 *
 * @category Notification
 * @package mhauri-slack
 * @author Marcel Hauri <marcel@hauri.me>, Sander Mangel <https://github.com/sandermangel>
 */

class Mhauri_Slack_Model_Observers_OnHold extends Mhauri_Slack_Model_Observers_Abstract
{
	/**
	 * Get customer name
	 * @param Object $_order
	 * @return String $customerName
	 */
	public function getCustomerName($_order)
	{
		$firstname = $_order->getCustomerFirstname();
		$lastname  = $_order->getCustomerLastname();
		$customerName = $firstname . ' ' . $lastname;
		return $customerName;
	}

    /**
     * Send a notification when a order was holded
     * @param $observer
     */
    public function notify($observer)
    {
        $_order = $observer->getEvent()->getOrder();
        $state = $_order->getState();
        $oldstate = $_order->getOrigData('state');

        $stateOnHold = Mage_Sales_Model_Order::STATE_HOLDED;
        
        $payment_method_code = $_order->getPayment()->getMethodInstance()->getCode();
        
        $order_url = Mage::helper('adminhtml')->getUrl('adminhtml/sales_order/view/order_id/',
        		['order_id'=> $_order->getEntityId()]);

        if ($state == $stateOnHold && $oldstate != $stateOnHold){
	        if($this->_getConfig(Mhauri_Slack_Model_Notification::ON_HOLD_PATH)) {
	        	Mage::log("oldstate ".$oldstate." state ". $state,null,'ikom.log');
	            $message = $this->_helper->__("*A order status has changed: On Hold.* \n*Order ID:* <%s|%s>, *Name:* %s, *Amount:* %s %s,\n*Status:* %s, *Payment Method:* %s",
	                $order_url,
	                $_order->getIncrementId(),
	                $this->getCustomerName($_order),
	                $_order->getGrandTotal(),
	                $_order->getOrderCurrencyCode(),
	            	$state,
	            	$payment_method_code
	            );
	
	            $this->_notificationModel
	                ->setMessage($message)
	                ->send();
	        }
    	}
	}
}