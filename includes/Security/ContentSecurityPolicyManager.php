<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Security;

use Waca\DataObjects\Domain;
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
    private ?Domain $currentDomain = null;

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

    public function setDomain(Domain $domain)
    {
        $this->currentDomain = $domain;
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
                            if ($this->currentDomain !== null) {
                                $policyIsSet = true;

                                $parts = parse_url($this->currentDomain->getWikiApiPath());
                                $bareHost = "${parts['scheme']}://${parts['host']}";

                                $constructedPolicy .= "{$bareHost} ";
                            }
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
