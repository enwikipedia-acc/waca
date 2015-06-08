 data-provide="typeahead" data-items="10" data-source="[{foreach $jsuserlist as $u}&quot;{$u|escape:'javascript'}&quot;{if $u@last}{else},{/if}{/foreach}]"
