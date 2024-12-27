{extends file="statistics/base.tpl"}
{block name="statisticsContent"}
    <div class="row">
        <div class="col-md-6">
            <h3>All-time top creators</h3>
            {include file="statistics/top-creators-table.tpl" dataTable=$queryAllTime}
        </div>
        <div class="col-md-6">
            <h3>Contents</h3>
            <ul>
                <li><a href="#alltimeactive">All-time active top creators</a></li>
                <li><a href="#today">Today's creators</a></li>
                <li><a href="#yesterday">Yesterday's creators</a></li>
                <li><a href="#lastweek">Last 7 days</a></li>
                <li><a href="#lastmonth">Last 28 days</a></li>
            </ul>

            <ul class="unstyled">
                <li><a href="#">Username</a> means an active account.</li>
                <li><a class="text-muted" href="#">Username</a> means a deactivated account.</li>
            </ul>

            <a id="alltimeactive"></a>
            <h3>All-time active top creators</h3>
            {include file="statistics/top-creators-table.tpl" dataTable=$queryAllTimeActive}

            <a id="today"></a>
            <h3>Today's creators</h3>
            {include file="statistics/top-creators-table.tpl" dataTable=$queryToday}

            <a id="yesterday"></a>
            <h3>Yesterday's creators</h3>
            {include file="statistics/top-creators-table.tpl" dataTable=$queryYesterday}

            <a id="lastweek"></a>
            <h3>Last 7 days</h3>
            {include file="statistics/top-creators-table.tpl" dataTable=$queryLast7Days}

            <a id="lastmonth"></a>
            <h3>Last 28 days</h3>
            {include file="statistics/top-creators-table.tpl" dataTable=$queryLast28Days}

        </div>
    </div>
{/block}
