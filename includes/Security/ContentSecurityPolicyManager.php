<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security;

use Waca\SiteConfiguration;

class ContentSecurityPolicyManager
{
    private $policy = [
        'default-src'     => [],
        'script-src'      => ['self', 'nonce'],
        'script-src-elem' => ['self', 'nonce'],
        'script-src-attr' => [],
        'connect-src'     => ['self'],
        'style-src'       => ['self'],
        'style-src-elem'  => ['self'],
        'style-src-attr'  => [],
        'img-src'         => ['self', 'data:', 'https://upload.wikimedia.org', 'https://accounts-dev.wmflabs.org/'],
        'font-src'        => ['self'],
        'form-action'     => ['self', 'oauth'],
        'frame-ancestors' => [],
    ];
    private $nonce = null;
    private $reportOnly = false;
    /**
     * @var SiteConfiguration
     */
    private $configuration;

    /**
     * ContentSecurityPolicyManager constructor.
     *
     * @param SiteConfiguration $configuration
     */
    public function __construct(SiteConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getNonce()
    {
        if ($this->nonce === null) {
            $this->nonce = base64_encode(openssl_random_pseudo_bytes(32));
        }

        return $this->nonce;
    }

    public function getHeader(): string
    {
        $reportOnly = '';
        if ($this->reportOnly) {
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
                            if ($this->nonce !== null) {
                                $constructedPolicy .= "'nonce-{$this->nonce}' ";
                            }
                            break;
                        case 'oauth':
                            $constructedPolicy .= "{$this->configuration->getOauthMediaWikiCanonicalServer()} ";
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

        if ($this->configuration->getCspReportUri() !== null) {
            $constructedPolicy .= 'report-uri ' . $this->configuration->getCspReportUri();
        }

        return $constructedPolicy;
    }
}
