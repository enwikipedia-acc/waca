<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Pages;

use Waca\Fragments\RequestData;
use Waca\Tasks\InternalPageBase;

class PageXffDemo extends InternalPageBase
{
    use RequestData;

    /**
     * @inheritDoc
     */
    protected function main()
    {
        $this->setTemplate('xffdemo.tpl');

        // requestHasForwardedIp == false
        // requestProxyData
        // requestRealIp == proxy
        // requestForwardedIp == xff header
        // forwardedOrigin  == top of the chain, assuming xff is trusted


        $this->assign('demo2', [
            [
                'trust' => true,
                'trustedlink' => true,
                'ip' => '172.16.0.164',
                'routable' => false,

            ], [
                'trust' => true,
                'ip' => '198.51.100.123',
                'routable' => true,
                'rdns' => 'trustedproxy.example.com',

            ], [
                'trust' => true,
                'ip' => '192.0.2.1',
                'routable' => true,
                'rdns' => 'client.users.example.org',
                'location' => [
                    'cityName' => 'San Francisco',
                    'regionName' => 'California',
                    'countryName' => 'United States'
                ],
                'showlinks' => true
            ]
        ]);

        $this->assign('demo3', [
            [
                'trust' => true,
                'trustedlink' => true,
                'ip' => '172.16.0.164',
                'routable' => false,

            ], [
                'trust' => false,
                'ip' => '198.51.100.234',
                'routable' => true,
                'rdns' => 'sketchyproxy.example.com',
                'showlinks' => true

            ], [
                'trust' => false,
                'ip' => '192.0.2.1',
                'routable' => true,
                'rdns' => 'client.users.example.org',
                'location' => [
                    'cityName' => 'San Francisco',
                    'regionName' => 'California',
                    'countryName' => 'United States'
                ],
                'showlinks' => true
            ]
        ]);

        $this->assign('demo4', [
            [
                'trust' => true,
                'trustedlink' => true,
                'ip' => '172.16.0.164',
                'routable' => false,

            ], [
                'trust' => true,
                'ip' => '198.51.100.123',
                'routable' => true,
                'rdns' => 'trustedproxy.example.com',
            ], [
                'trust' => false,
                'ip' => '198.51.100.234',
                'routable' => true,
                'rdns' => 'sketchyproxy.example.com',
                'showlinks' => true
            ], [
                'trust' => false,
                'trustedlink' => true,
                'ip' => '198.51.100.124',
                'routable' => true,
                'rdns' => 'trustedproxy2.example.com',
                'showlinks' => true
            ], [
                'trust' => false,
                'ip' => '192.0.2.1',
                'routable' => true,
                'rdns' => 'client.users.example.org',
                'location' => [
                    'cityName' => 'San Francisco',
                    'regionName' => 'California',
                    'countryName' => 'United States'
                ],
                'showlinks' => true
            ]
        ]);

        $this->assign('demo1', [
            [
                'trust' => true,
                'trustedlink' => true,
                'ip' => '172.16.0.164',
                'routable' => false,

            ], [
                'trust' => true,
                'trustedlink' => true,
                'ip' => '192.0.2.1',
                'routable' => true,
                'rdns' => 'client.users.example.org',
                'location' => [
                    'cityName' => 'San Francisco',
                    'regionName' => 'California',
                    'countryName' => 'United States'
                ],
                'showlinks' => true
            ]
        ]);
    }
}
