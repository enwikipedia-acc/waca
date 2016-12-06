{extends file="base.tpl"}
{block name="content"}
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span12">
                <div class="page-header"><h1>Oops...</h1></div>
                <p>Something went wrong with your last action.</p>
                {include file="alert.tpl" alertblock=true alertclosable=false alerttype="alert-error"
                    alertheader="" alertmessage=$message }
                <p>Please use your browser's back button to go back to where you were and attempt to fix the error.</p>
            </div>
        </div>
    </div>
{/block}