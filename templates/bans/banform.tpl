{extends file="pagebase.tpl"}
{block name="content"}
    <div class="row">
        <div class="col-md-12" >
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Ban Management <small class="text-muted">View, ban, and unban requesters</small></h1>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <h3>Ban an IP, name, or email address</h3>

            <form method="post">
                {include file="security/csrf.tpl"}

                <div class="form-group row">
                    <div class="col-sm-4 col-md-3 col-xl-2">
                        <label for="banType" class="col-form-label">Type:</label>
                    </div>
                    <div class="col-sm-8 col-md-5 col-xl-4">
                        <select class="form-control" name="type" required="required" id="banType">
                            <option value="IP"{if $bantype == "IP"} selected="selected"{elseif $bantype != ""} disabled="disabled"{/if}>
                                IP
                            </option>
                            <option value="Name"{if $bantype == "Name"} selected="selected"{elseif $bantype != ""} disabled="disabled"{/if}>
                                Name
                            </option>
                            <option value="EMail"{if $bantype == "EMail"} selected="selected"{elseif $bantype != ""} disabled="disabled"{/if}>
                                E-Mail
                            </option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-4 col-md-3 col-xl-2">
                        <label for="banTarget" class="col-form-label">Ban Target:</label>
                    </div>
                    <div class="col-sm-8 col-md-5 col-xl-4">
                        <input type="text" class="form-control" id="banTarget"name="target" {if $bantarget != ""} readonly="readonly" value="{$bantarget|escape}"{/if} required="required"/>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-4 col-md-3 col-xl-2">
                        <label for="banReason" class="col-form-label">Ban Reason:</label>
                    </div>
                    <div class="col-sm-8 col-md-5 col-xl-4">
                        <input type="text" class="form-control" id="banReason" name="banreason" required="required"/>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-4 col-md-3 col-xl-2">
                        <label for="banDuration" class="col-form-label">Duration:</label>
                    </div>
                    <div class="col-sm-8 col-md-5 col-xl-4">
                        <select class="form-control" name="duration" required="required" id="banDuration">
                            <option value="-1">Indefinite</option>
                            <option value="86400">24 Hours</option>
                            <option value="604800">One Week</option>
                            <option value="2629743">One Month</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-4 col-md-3 col-xl-2">
                        <label for="otherDuration" class="col-form-label">Other duration:</label>
                    </div>
                    <div class="col-sm-8 col-md-5 col-xl-4">
                        <input class="form-control" type="text" id="otherDuration" name="otherduration"/>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="offset-sm-4 offset-md-3 offset-xl-2 col-sm-8 col-md-5 col-xl-4">
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-ban"></i>&nbsp;Ban
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
{/block}
