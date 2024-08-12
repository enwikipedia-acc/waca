<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
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
        'frame-ancestors' => ['self'],
        'frame-src'       => ['self'],
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
            $policyIsSet = false;

            if (count($values) > 0) {
                foreach ($values as $value) {
                    switch ($value) {
                        case 'none':
                        case 'self':
                        case 'strict-dynamic':
                            $policyIsSet = true;
                            $constructedPolicy .= "'{$value}' ";
                            break;
                        case 'nonce':
                            if ($this->nonce !== null) {
                                $policyIsSet = true;
                                $constructedPolicy .= "'nonce-{$this->nonce}' ";
                            }
                            break;
                        case 'oauth':
                            $policyIsSet = true;
                            $constructedPolicy .= "{$this->configuration->getOauthMediaWikiCanonicalServer()} ";
                            break;
                        default:
                            $policyIsSet = true;
                            $constructedPolicy .= $value . ' ';
                            break;
                    }
                }

                if (!$policyIsSet) {
                    $constructedPolicy .= "'none' ";
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
