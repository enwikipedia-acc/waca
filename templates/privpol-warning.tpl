{extends file="base.tpl"}
{block name="content"}
    <div class="row-fluid">
        <h2>Privacy Policy Warning!</h2>
        <form action="{$baseurl}/internal.php/viewRequest/comment" method="post" class="form-horizontal span8">

            {include file="alert.tpl" alertblock=false alertclosable=false alerttype="alert-error" alertheader=""
            alertmessage="<strong>Please read this information carefully.</strong>"}

            <p>
                The Wikimedia Foundation has requested that the ACC project be compliant with the Foundation's
                <a href="https://meta.wikimedia.org/wiki/Privacy_policy">Privacy Policy</a>, specifically with regards
                to the <a href="https://meta.wikimedia.org/wiki/Privacy_policy#protection-duration">retention of
                    non-public personally identifying information</a>, such as requesters' email addresses and IP
                addresses.
            </p>
            <p>
                To summarize, ACC may only store this data in special locations designed to handle this data, and these
                locations are automatically scrubbed periodically to ensure that non-public data is retained for no
                longer than it needs to be. The comments section is not considered one of these "secured" locations, as
                implementing automatic scrubbing for comments is both infeasible and undesirable.
            </p>
            <p>
                These restrictions are the same reason why you must now be
                <a href="https://meta.wikimedia.org/wiki/Identification_noticeboard">"Identified"</a> to the Wikimedia
                Foundation to be able to access ACC.
            </p>
            <p>
                The software has detected what appears to be an IP address in text of the comment you have just
                attempted to post (the request ID and content of the comment is displayed below).
            </p>
            <p>
                Since not all IP addresses are considered non-public for the purposes of the Privacy Policy, you may
                override this warning if you choose and post your comment anyway. Before doing so, though, you must read
                the acknowledgement in red below and check the box to confirm your understanding. You should also at
                this point (re-)read DeltaQuad's <a href="https://accounts-dev.wmflabs.org/other/identinfoemail.html">
                    email regarding what is and is not acceptable under the Privacy Policy</a>.
            </p>
            <p>
                If you are not sure whether or not the IP address in your comment is okay to be posted, please either
                play it safe and simply not post the IP address, or ask an ACC administrator. <strong>Good faith will
                    <em>not</em> be assumed with privacy policy violations.</strong>
            </p>
            <p>
                If you are absolutely certain that you want to go ahead and post this comment, either because it does
                not actually contain an IP address or because any IP address(es) contained within are not considered
                non-public, check the box below and click the button to proceed.
            </p>

            <div class="control-group">
                <label for="displayid" class="control-label">Request ID:</label>
                <div class="controls">
                    <input type="text" name="displayid" id="displayid" value="{$request->getId()}" disabled="disabled"/>
                    <input type="hidden" name="request" value="{$request->getId()}"/>
                </div>
            </div>

            <div class="control-group">
                <label for="comment-display" class="control-label">Comment text:</label>
                <div class="controls">
                    <textarea name="comment-display" id="comment-display" class="input-xxlarge" disabled="disabled"
                              rows="6">{$comment|escape}</textarea>
                    <input type="hidden" name="comment" value="{$comment|escape}"/>
                </div>
            </div>

            {include file="alert.tpl" alertblock=false alertclosable=false alerttype="alert-info" alertheader=""
            alertmessage="If you wish to cancel or edit your comment, please use the 'back' button in your browser now."}

            <div class="control-group">
                <div class="controls">
                    <label class="checkbox">
                        <input type="checkbox" id="inputActive" name="privpol-check-override" required="required"/>
                        <p>
                            By checking this box, I confirm that I understand that personally identifying information,
                            such as IP addresses of requesters, is not to be posted to the comments section of an ACC
                            request, and I assert that the IP address (or IP address-like text) in my comment is not
                            considered personally identifying information.
                        </p>
                        <p>
                            <span class="text-error"><strong>I furthermore understand that posting personally identifying information to the
                                comments of an ACC request may result in my permanent suspension from the ACC project,
                                at the discretion of the ACC administrators, and that Wikimedia Foundation Legal will be
                                advised of all Privacy Policy violations, which may lead to the loss of my Identified
                                status with the Foundation should I be found to have violated the Privacy
                                Policy.</strong></span></p>
                    </label>
                </div>
            </div>

            <div class="control-group">
                <div class="controls">
                    <button type="submit" class="btn btn-warning">Proceed</button>
                </div>
            </div>
        </form>
    </div>
{/block}
