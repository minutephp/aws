<div class="content-wrapper ng-cloak" ng-app="databaseConfigApp" ng-controller="databaseConfigController as mainCtrl" ng-init="init()">
    <div class="admin-content">
        <section class="content-header">
            <h1>
                <span translate="">Database setup</span>
            </h1>

            <ol class="breadcrumb">
                <li><a href="" ng-href="/admin"><i class="fa fa-dashboard"></i> <span translate="">Admin</span></a></li>
                <li class="active"><i class="fa fa-cog"></i> <span translate="">Database</span></li>
            </ol>
        </section>

        <section class="content">
            <form id="databaseForm" method="post" action="/admin/aws/db/copy" target="copyFrame">
                <div class="box box-{{databaseForm.$valid && 'success' || 'danger'}}">
                    <div class="box-header with-border">
                        <span translate="">Sync local database to RDS instance</span>
                    </div>

                    <div class="box-body">
                        <div ng-show="!data.loading">
                            <div class="form-group">
                                <p class="help-block">
                                    <span translate="">Click "Initialize" to copy your local database to your RDS instance. After that automatically keep all your databases in sync using </span>
                                    <a google-search="phinx migrations">Phinx migrations</a>
                                </p>
                            </div>

                            <div class="form-group">
                                <button type="button" class="btn btn-flat btn-primary btn-lg" ng-click="mainCtrl.mirror()">
                                    <i class="fa fa-bolt"></i> <span translate="">Initialize RDS instance</span>
                                </button>
                            </div>

                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" ng-model="data.tweak" name="tweak" value="true"> <span translate="">Tweak database for better performance</span>
                                    </label>
                                </div>
                            </div>

                            <hr>

                            <p class="help-block">
                                <i class="fa fa-exclamation-triangle"></i>
                                <span translate="">This will erase all data on your current RDS instance.</span>
                            </p>
                        </div>

                        <div ng-show="!!data.loading">
                            <p class="help-block"><i class="fa fa-caret-right"></i> <span translate="">Initializing RDS instance using local database..</span></p>
                            <iframe src="about:blank" width="100%" height="400" name="copyFrame" id="frameProgress" frameborder="no" class="panel panel-default padded"></iframe>
                            <label><input type="checkbox" ng-model="data.scroll"> Disable auto-scroll</label>
                        </div>

                        <!--<pre>{{settings | json}}</pre>-->
                    </div>

                </div>
            </form>
        </section>
    </div>
</div>
