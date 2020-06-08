{extends file="modals/base.tpl"}
{block name="modalId"}modalDiffHelp{/block}
{block name="modalTitle"}Help{/block}
{block name="modalBody"}
    <p>
        Please make an edit to your talk page while logged in. In this edit, note that you requested an account
        on the ACC account creation interface. Failure to do this will result in your request being declined as
        we will be unable to verify it is you who requested the account.
    </p>
    <p>
        Enter the revid of this confirmation edit in this field. (The revid is the number after the
        <code>&amp;diff=</code> part of the URL of a diff.
    </p>
{/block}
