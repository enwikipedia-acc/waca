{extends file="statistics/base.tpl"}
{block name="statisticsContent"}
    {foreach from=$lists item="userlist" key="title"}
        <h3>{$title|escape}</h3>
        <ul>
            {foreach from=$userlist item="user"}
                <li><a href="{$baseurl}/internal.php/statistics/users/detail?user={$user->getId()}">{$user->getUsername()|escape}</a></li>
            {/foreach}
        </ul>
    {/foreach}
{/block}