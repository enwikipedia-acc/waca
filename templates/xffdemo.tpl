{extends file="pagebase.tpl"}
{block name="content"}
    <div id="pageRequestLog">
        <div class="row">
            <div class="col-md-12" >
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">X-Forwarded-For demo <small class="text-muted">Help on interpreting the XFF data</small></h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="offset-lg-2 offset-xl-3 col-lg-8 col-xl-6">
                <p class="lead">
                    When handling requests, you are likely to come across several different possible scenarios when looking
                    at the IP address section. This page aims to guide you through what the tool is trying to tell you.
                </p>
                <p>
                    When the tool receives a request from a newbie, the tool records the newbie's IP address as seen by the
                    server on which ACC runs. However, there is sometimes a proxy between the tool and the newbie - when
                    that is the case, the IP address seen by the server is that of the proxy, not the newbie. Some proxy
                    servers include a special header, known as <code>X-Forwarded-For</code>, in which they append the IP
                    address of the end user to this header before passing the request on. If there are multiple proxy
                    servers, multiple IP addresses get added to this header.
                </p>
                <p>
                    If there is no proxy server, this is what you would see:
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-12 px-3 mb-3">
                <div class="card card-body border border-dark rounded p-3">
                    {include file="view-request/ip-section.tpl" requestHasForwardedIp=false requestTrustedIp="1.2.3.4"}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="offset-lg-2 offset-xl-3 col-lg-8 col-xl-6">
                <p>
                    This scenario, however, is not likely to ever appear for the simple reason that Wikimedia Cloud Services
                    have placed their own proxy server in front of the tool. Thus, there will always be at least one proxy
                    server.
                </p>
                <h3>97% of requests: Wikimedia proxy only</h3>
                <p>
                    When there is only the <abbr title="Wikimedia Cloud Services">WMCS</abbr> proxy in place, this is what you are likely to see something more like this:
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-12 px-3 mb-3">
                <div class="card card-body border border-dark rounded p-3">
                    {include file="view-request/ip-section.tpl" requestHasForwardedIp=true requestForwardedIp="192.0.2.1" requestRealIp="172.16.0.164" forwardedOrigin="192.0.2.1" requestProxyData=$demo1}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="offset-lg-2 offset-xl-3 col-lg-8 col-xl-6">
                <p>
                    The above scenario has passed through one server we trust (<code>172.16.0.164</code>), and that
                    server
                    has indicated it received a request from <code>190.0.2.1</code>. At this point, the XFF chain stops,
                    and we assume that <code>192.0.2.1</code> is the client's IP address.
                </p>
                <p>
                    We also augment each IP address with some extra information, including the estimated location of the
                    IP address, and the reverse DNS name of the IP address. These can help give some indication of the
                    type of IP address - whether it's a residential ISP, a hosting provider, or otherwise.
                </p>
                <h3>2% of requests - another trusted proxy</h3>
                <p>
                    Occasionally, we will get a request from a user who is using a proxy service which we trust to provide
                    accurate XFF headers. It's worth noting here that Wikimedia takes the XFF headers as truth where the
                    provider of the XFF header is trusted. We use the same trust list here, but we display all the data
                    anyway. The essence of it is that the last "trusted" IP address in the list will be the one that
                    Wikimedia uses.
                </p>
                <p>
                    In this example, the user has passed through a trusted proxy provider before hitting the WMCS proxy.
                    Both proxy servers are listed and marked as trusted, and we don't show the IP links for those trusted
                    proxy servers.
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-12 px-3 mb-3">
                <div class="card card-body border border-dark rounded p-3">
                    {include file="view-request/ip-section.tpl" requestHasForwardedIp=true requestForwardedIp="192.0.2.1, 198.51.100.123" requestRealIp="172.16.0.164" forwardedOrigin="192.0.2.1" requestProxyData=$demo2}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="offset-lg-2 offset-xl-3 col-lg-8 col-xl-6">
                <h3>0.5% of requests - untrusted proxies</h3>
                <p>
                    Conversely to the above, sometimes people will request an account from a proxy server which publishes
                    the client IP address, but not one that we trust to be accurate.
                </p>
                <p>
                    As each server on the chain of proxies has the option to rewrite the entire XFF header, we should not
                    use any entries from servers we don't trust. If we trust a server to not forge the XFF header, we can
                    trust the next item in the chain is accurate, but no further. It is at this point that the trust chain
                    breaks, and we start seeing lots of red flags.
                </p>
            </div>
        </div>


        <div class="row">
            <div class="col-12 px-3 mb-3">
                <div class="card card-body border border-dark rounded p-3">
                    {include file="view-request/ip-section.tpl" requestHasForwardedIp=true requestForwardedIp="192.0.2.1, 198.51.100.234" requestRealIp="172.16.0.164" forwardedOrigin="192.0.2.1" requestProxyData=$demo3}
                </div>
            </div>
        </div>


        <div class="row">
            <div class="offset-lg-2 offset-xl-3 col-lg-8 col-xl-6">
                <p>
                    In this scenario, because we trust the first IP address, we can believe that it's correct in telling
                    us that <code>198.51.100.234</code> is indeed where the request has come from. However, we cannot
                    trust that server is telling the truth.
                </p>
                <p>
                    The second server is reporting that the third server exists, but Wikimedia will only believe that the
                    client is on the second IP address, and so this is the IP address we should check. It is worth checking
                    the third too, because it's possible that the trust lists between ACC and Wikimedia have drifted.
                </p>
                <h3>0% of requests - an insane possibility</h3>
                <p>
                    If someone really wants to play with the XFF headers, they are welcome to do so as they send in the
                    request. If they're putting crazy things in the header, it's not outside the realm of possibility that
                    something like this will occur:
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-12 px-3 mb-3">
                <div class="card card-body border border-dark rounded p-3">
                    {include file="view-request/ip-section.tpl" requestHasForwardedIp=true requestForwardedIp="192.0.2.1, 198.51.100.234" requestRealIp="172.16.0.164" forwardedOrigin="192.0.2.1" requestProxyData=$demo4}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="offset-lg-2 offset-xl-3 col-lg-8 col-xl-6">
                <p>
                    This example has three trusted servers - <code>172.16.0.164</code>, <code>198.51.100.123</code>, and
                    <code>198.51.100.124</code>. However, this indicates the breaking of the trust chain - because the
                    request passed through an untrusted server, we can't tell if the untrusted server is forging the information
                    leading to the trusted server. As such, we indicate that the <code>198.51.100.124</code> would be
                    trusted, had the trust chain not already been broken.
                </p>
                <p>
                    In this case, the IP address that Wikipedia will see is <code>198.51.100.234</code> - the first untrusted
                    IP address in the list.
                </p>
            </div>
        </div>

    </div>
    <div class="row">
        <div class="offset-lg-2 offset-xl-3 col-lg-8 col-xl-6 text-muted">
            <h5><small>Notes on percentage estimates</small></h5>
            <p><small>
                    The estimates of request volumes in this documentation is a vague guess only based on intuition of a
                    long-term ACC user, and is quite likely to be inaccurate.
                </small></p>
            <h5><small>Notes on privacy</small></h5>
            <p><small>
                This documentation uses IP addresses in the <a href="https://tools.ietf.org/html/rfc1918">RFC 1918</a>
                range 172.16.0.0/12 to represent internal Wikimedia IP addresses, and IP address in the
                <a href="https://tools.ietf.org/html/rfc5737">RFC 5737</a> ranges 192.0.2.0/24 and 198.51.100.0/24 to represent external IP
                addresses. Both of these address blocks are not routable on the public internet, and thus do not have
                any privacy implications by being part of this documentation.
                </small></p>
        </div>
    </div>
{/block}
