﻿{extends file="statistics/base.tpl"}
{block name="statisticsContent"}
    <p>This list contains the usernames of all accounts that have not logged in in the past 45 days.</p>
    {if ! $showImmune}<p>Tool root and checkuser-flagged accounts are hidden from this list.</p>{/if}
    <table class="table table-striped table-hover table-condensed">
        <thead>
        <tr>
            <th>User ID</th>
            <th>Tool Username</th>
            <th>User access level</th>
            <th>Checkuser?</th>
            <th>On-wiki username</th>
            <th>Last activity</th>
            <th>Approval</th>
            {if $currentUser->isAdmin()}
                <th>Suspend</th>
            {/if}
        </tr>
        </thead>
        <tbody>
        {foreach from=$inactiveUsers item="user"}
            {if $user->isCheckuser() && !$showImmune}
            {else}
                <tr>
                    <td>{$user->getId()}</td>
                    <td>{$user->getUsername()|escape}</td>
                    <td>{$user->getStatus()}</td>
                    <td>{if $user->isCheckuser()}Yes{else}No{/if}</td>
                    <td>{$user->getOnWikiName()|escape}</td>
                    <td>{$user->getLastActive()} <span class="muted">{$user->getLastActive()|relativedate}</span></td>
                    <td>{if $user->getApprovalDate() != false}{$user->getApprovalDate()->format("Y-m-d H:i:s")} <span class="muted">{$user->getApprovalDate()|relativedate}</span>{/if}</td>
                    {if $currentUser->isAdmin()}
                        <td>
                            {if ! $user->isCheckuser()}
                                <a class="btn btn-danger btn-small"
                                   href="{$baseurl}/internal.php/userManagement/suspend?user={$user->getId()}&amp;preload=Inactive%20for%2045%20or%20more%20days.%20Please%20contact%20a%20tool%20admin%20if%20you%20wish%20to%20come%20back.">
                                    <i class="icon-ban-circle icon-white"></i> Suspend!
                                </a>
                            {/if}
                        </td>
                    {/if}
                </tr>
            {/if}
        {/foreach}
        </tbody>
    </table>
{/block}