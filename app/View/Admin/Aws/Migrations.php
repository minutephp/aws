<div class="content-wrapper ng-cloak" ng-app="migrationListApp" ng-controller="migrationListController as mainCtrl" ng-init="init()">
    <div class="admin-content">
        <section class="content-header">
            <h1><span translate="">Database migrations</span> <small><span translate="">info</span></small></h1>

            <ol class="breadcrumb">
                <li><a href="" ng-href="/admin"><i class="fa fa-dashboard"></i> <span translate="">Admin</span></a></li>
                <li class="active"><i class="fa fa-migration"></i> <span translate="">Migration list</span></li>
            </ol>
        </section>

        <section class="content">
            <minute-event name="IMPORT_MIGRATION_STATUS" as="data.phinx"></minute-event>

            <form name="migrationForm" id="runner" action="/admin/aws/migrations/run" method="POST" target="runMigrations"></form>

            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <span translate="">Migrations status</span>
                    </h3>
                </div>

                <div class="box-body">
                    <div class="list-group">
                        <div class="list-group-item list-group-item-bar list-group-item-bar-{{migration.type === 'up' && 'success' || 'danger'}}"
                             ng-repeat="migration in data.phinx.migrations">
                            <div class="pull-left">
                                <h4 class="list-group-item-heading">{{migration.name | ucfirst}}</h4>
                                <p class="list-group-item-text hidden-xs" ng-if="migration.type === 'up'">
                                    <span translate="">Completed </span> {{migration.started | timeAgoStr}}
                                </p>
                            </div>
                            <div class="md-actions pull-right">
                                <ng-switch on="migration.type === 'up'">
                                    <span ng-switch-when="true"><i class="fa fa-check-circle-o"></i> <span translate="">Complete</span></span>
                                    <span ng-switch-when="false"><i class="fa fa-circle-o"></i> <span translate="">Pending</span></span>
                                </ng-switch>
                            </div>

                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>

                <div class="box-footer" ng-show="data.phinx.pending">
                    <button type="button" class="btn btn-flat btn-primary" ng-click="mainCtrl.run()" ng-show="!data.running">
                        <i class="fa fa-bolt"></i> <span translate="">Run pending migrations on RDS instance</span>
                    </button>

                    <div ng-show="data.running">
                        <iframe src="about:blank" width="100%" height="200" name="runMigrations" frameborder="no" class="panel panel-default"></iframe>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
