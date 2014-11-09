<div class="row-fluid">
    <h2>Privacy Policy Warning!</h2>
    <form action="{$baseurl}/acc.php" method="post" class="form-horizontal span8">
        <div class="control-group">
            <label for="displayid" class="control-label">Request ID:</label>
            <div class="controls">
                <input type="text" name="displayid" value="{$request->getId()}" disabled="disabled"/>
                <input type="hidden" name="id" value="{$request->getId()}" />
            </div>
        </div>
        <div class="control-group">
            <label for="comment" class="control-label">Comment text:</label>
            <div class="controls">
                <textarea name="comment" class="input-xxlarge" disabled="disabled" rows="6">{$comment}</textarea>
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
            		<p><strong>If you wish to cancel or edit your comment, please use the "back" button in your browser now.</strong></p>
            		<p><input type="checkbox" name="privpol-check-override" value="override" /><label for="privpol-check-override" class="control-label" style="color: #F00;">By checking this box, I confirm that I understand that personally identifying information, such as IP addresses of requesters, is not to be posted to the comments section of an ACC request, and I assert that the IP address (or IP address-like text) in my comment is not considered personally identifying information.  <strong>I furthermore understand that posting personally identifying information to the comments of an ACC request may result in my permanent suspension from the ACC project, at the discretion of the ACC administrators, and that Wikimedia Foundation Legal will be advised of all Privacy Policy violations, which may lead to the loss of my Identified status with the Foundation.</strong></label></p> 
                <p><button type="submit" class="btn btn-warning">Proceed</button></p>
            </div>
        </div>
    </form>
</div>
