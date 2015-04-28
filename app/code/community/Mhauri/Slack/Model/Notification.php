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
 * @author Marcel Hauri <marcel@hauri.me>
 */

class Mhauri_Slack_Model_Notification extends Mage_Core_Model_Abstract
{

    const LOG_FILE                      = 'slack.log';
    const DEFAULT_SENDER                = 'Magento Slack';

    const ENABLE_NOTIFICATION_PATH      = 'slack/general/enable_notification';
    const ENABLE_LOG_PATH               = 'slack/general/enable_log';

    const WEBHOOK_URL_PATH              = 'slack/api/webhook_url';
    const CHANNEL_PATH                  = 'slack/api/channel';
    const USERNAME_PATH                 = 'slack/api/username';

    const NEW_ORDER_PATH                = 'slack/notification/new_order';
    const NEW_CUSTOMER_ACCOUNT_PATH     = 'slack/notification/new_customer_account';
    const ADMIN_USER_LOGIN_FAILED_PATH  = 'slack/notification/admin_user_login_failed';

    /**
     * Store the Message
     * @var string
     */
    private $_message       = '';

    /**
     * Store the from name
     * @var string
     */
    private $_channel       = null;

    /**
     * Store room id
     * @var null
     */
    private $_username      = null;

    /**
     * Store webhook url
     * @var null
     */
    private $_webhook        = null;


    public function _construct()
    {
        $this->setWebhookUrl(Mage::getStoreConfig(self::WEBHOOK_URL_PATH, 0));
        $this->setUsername(Mage::getStoreConfig(self::USERNAME_PATH, 0));
        $this->setChannel(Mage::getStoreConfig(self::CHANNEL_PATH, 0));

        if($this->isEnabled()) {
            $this->_webhook = Mage::getStoreConfig(self::WEBHOOK_URL_PATH, 0);
        }
        parent::_construct();
    }

    /**
     * @param $webhook
     * @return $this
     */
    public function setWebhookUrl($webhook)
    {
        if(is_string($webhook)) {
            $this->_token = $webhook;
        }

        return $this;
    }

    /**
     * @return null|string
     */
    public function getWebhookUrl()
    {
        return $this->_webhook;
    }

    /**
     * @param $channel
     * @return $this
     */
    public function setChannel($channel)
    {
        if(is_string($channel)) {
            $this->_channel = $channel;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        if($this->_channel) {
            return $this->_channel;
        }

        return '';
    }

    /**
     * @param $username
     * @return $this
     */
    public function setUsername($username)
    {
        if(is_string($username)) {
            $this->_username = $username;
        }

        return $this;
    }

    /**
     * @return null
     */
    public function getUsername()
    {
        return $this->_username;
    }

    /**
     * @param $message
     * @return $this
     */
    public function setMessage($message)
    {
        if(is_string($message)) {
            $this->_message = $message;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * @return mixed
     */
    public function isEnabled()
    {
        return Mage::getStoreConfig(self::ENABLE_NOTIFICATION_PATH, 0);
    }

    /**
     * send message to room
     */
    public function send()
    {

        $params = array(
            'channel'   => $this->getChannel(),
            'username'  => $this->getUsername(),
            'text'      => $this->getMessage(),
            'mrkdwn'    => true,
            'mrkdwn_in' => '["text"]'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_NOBODY, 0);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $this->getWebhookUrl());
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('payload' => json_encode($params)));


        if(curl_exec($ch)) {
            if(Mage::getStoreConfig(self::ENABLE_LOG_PATH, 0)) {
                Mage::log('Message sent: ' . $this->getMessage(), Zend_Log::INFO, self::LOG_FILE, true);
            }
        } else {
            $params = array(
                'webhook_url:'  => $this->getWebhookUrl(),
                'channel:'      => $this->getChannel(),
                'username:'     => $this->getUsername(),
            );
            Mage::log($params, Zend_Log::ERR, self::LOG_FILE, true);
        }
        curl_close($ch);
    }
}
