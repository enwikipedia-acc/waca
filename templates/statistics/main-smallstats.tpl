<div class="span6">
  <h4>Statistics</h4>
  <table class="table table-striped table-condensed">
    <tr>
      <th>Open Requests</th>
      <td>{$statsOpen}</td>
    </tr>
    <tr>
      <th>Requests needing an account creator</th>
      <td>{$statsAdmin}</td>
    </tr>
    <tr>
      <th>Requests needing a checkuser</th>
      <td>{$statsCheckuser}</td>
    </tr>
    <tr>
      <th>Unconfirmed requests</th>
      <td>{$statsUnconfirmed}</td>
    </tr>
    <tr>
      <th>Tool administrators</th>
      <td>{$statsAdminUsers}</td>
    </tr>
    <tr>
      <th>Tool users</th>
      <td>{$statsUsers}</td>
    </tr>
    <tr>
      <th>Tool suspended users</th>
      <td>{$statsSuspendedUsers}</td>
    </tr>
    <tr>
      <th>New tool users</th>
      <td>{$statsNewUsers}</td>
    </tr>
    <tr>
      <th>Request with most comments</th>
      <td>
        <a href="{$baseurl}/acc.php?action=zoom&amp;id={$mostComments}">{$mostComments}</a>
      </td>
    </tr>
  </table>
</div>