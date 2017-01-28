<div class="content-wrapper ng-cloak" ng-app="CronApp" ng-controller="CronController as mainCtrl" ng-init="init()" ng-cloak="">

<section class="content debug">
<!-- var dump start -->
<div class="panel panel-default">
    <div class="panel-heading"><b>configs</b></div>
    <div class="panel-body">
        <pre class="pre-scrollable">{{configs.dump() | json}}</pre>
    </div>
</div>

<!-- var dump end -->
</section>

</div>
