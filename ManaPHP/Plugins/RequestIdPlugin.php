<?php

namespace ManaPHP\Plugins;

use ManaPHP\Event\EventArgs;
use ManaPHP\Http\Client\Request;
use ManaPHP\Plugin;

class RequestIdPlugin extends Plugin
{
    /**
     * @var bool
     */
    protected $_enabled = true;

    /**
     * RequestIdPlugin constructor.
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        if (isset($options['enabled'])) {
            $this->_enabled = (bool)$options['enabled'];
        }

        if (MANAPHP_CLI) {
            $this->_enabled = false;
        }

        if ($this->_enabled) {
            $this->attachEvent('httpClient:requesting', [$this, 'onHttpClientRequesting']);
        }
    }

    public function onHttpClientRequesting(EventArgs $eventArgs)
    {
        /** @var Request $request */
        $request = $eventArgs->data;

        if (!isset($request->headers['X-Request-Id'])) {
            $request->headers['X-Request-Id'] = $this->request->getRequestId();
        }
    }
}