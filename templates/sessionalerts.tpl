{if count($alerts) > 0}
    {foreach $alerts as $a}
        {include file="alert.tpl" alertblock=$a->isBlock() alertclosable=$a->isClosable() alerttype=$a->getType()
        alertheader=$a->getTitle() alertmessage=$a->getMessage() }
    {/foreach}
{/if}