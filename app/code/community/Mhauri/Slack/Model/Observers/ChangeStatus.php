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

class Mhauri_Slack_Model_Observers_ChangeStatus extends Mhauri_Slack_Model_Observers_Abstract
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
     * Send a notification when a new order was placed
     * @param $observer
     */
    public function change_status_notify($observer)
    {
        $_order = $observer->getEvent()->getOrder();
        $status = $_order->getStatus();
        $oldstatus=$_order->getOrigData('status');
        
        $statusProcessing = 'processing';
        $statusOnHold = 'holded';
        
        $payment_method_code = $_order->getPayment()->getMethodInstance()->getCode();
        
        $order_url = Mage::helper('adminhtml')->getUrl('adminhtml/sales_order/view/order_id/',
        		['order_id'=> $_order->getEntityId()]);

        /*Se lo status non era processing e lo divenda inviamo la mail*/
        if ($status == $statusProcessing && $oldstatus != $statusProcessing){
	        if($this->_getConfig(Mhauri_Slack_Model_Notification::NEW_ORDER_PATH)) {
	            $message = $this->_helper->__("*A new order has been placed.* \n*Order ID:* <%s|%s>, *Name:* %s, *Amount:* %s %s,\n*Status:* %s, *Payment Method:* %s",
	                $order_url,
	                $_order->getIncrementId(),
	                $this->getCustomerName($_order),
	                $_order->getGrandTotal(),
	                $_order->getOrderCurrencyCode(),
	            	$status,
	            	$payment_method_code
	            );
	
	            $this->_notificationModel
	                ->setMessage($message)
	                ->send();
	        }
        }elseif ($status == $statusOnHold && $oldstatus != $statusOnHold){
	        if($this->_getConfig(Mhauri_Slack_Model_Notification::ON_HOLD_PATH)) {
	            $message = $this->_helper->__("*A order has been holded.* \n*Order ID:* <%s|%s>, *Name:* %s, *Amount:* %s %s,\n*Status:* %s, *Payment Method:* %s",
	                $order_url,
	                $_order->getIncrementId(),
	                $this->getCustomerName($_order),
	                $_order->getGrandTotal(),
	                $_order->getOrderCurrencyCode(),
	            	$status,
	            	$payment_method_code
	            );
	
	            $this->_notificationModel
	                ->setMessage($message)
	                ->send();
	        }
    	}
	}
}