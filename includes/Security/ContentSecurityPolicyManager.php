<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security;

class ContentSecurityPolicyManager
{
    private $policy = [
        'default-src'     => [],
        'script-src-elem' => ['self', 'nonce'],
        'connect-src'     => ['self'],
        'style-src-elem'  => ['self'],
        'img-src'         => ['self', 'data:', 'https://upload.wikimedia.org'],
        'font-src'        => ['self'],
        'form-action'     => ['self'],
        'frame-ancestors' => [],
    ];

    private $nonce = null;
    private $reportOnly = false;

    public function getNonce()
    {
        if($this->nonce === null) {
            $this->nonce = base64_encode(openssl_random_pseudo_bytes(32));
        }

        return $this->nonce;
    }

    public function getHeader() : string
    {
        $reportOnly = '';
        if($this->reportOnly) {
            $reportOnly = '-Report-Only';
        }

        $constructedPolicy = "Content-Security-Policy{$reportOnly}: ";

        foreach ($this->policy as $item => $values) {
            $constructedPolicy .= $item . ' ';

            if (count($values) > 0) {
                foreach ($values as $value) {
                    switch ($value) {
                        case 'none':
                        case 'self':
                        case 'strict-dynamic':
                            $constructedPolicy .= "'{$value}' ";
                            break;
                        case 'nonce':
                            if($this->nonce !== null) {
                                $constructedPolicy .= "'nonce-{$this->nonce}' ";
                            }
                            break;
                        default:
                            $constructedPolicy .= $value . ' ';
                            break;
                    }
                }
            }
            else {
                $constructedPolicy .= "'none' ";
            }

            $constructedPolicy .= '; ';
        }

        return $constructedPolicy;
    }
}
