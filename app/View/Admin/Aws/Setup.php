<div class="content-wrapper ng-cloak" ng-app="awsEditApp" ng-controller="awsEditController as mainCtrl" ng-init="init()">
    <div class="admin-content">
        <section class="content-header">
            <h1>
                <span translate="">AWS Setup</span>
            </h1>

            <ol class="breadcrumb">
                <li><a href="" ng-href="/admin"><i class="fa fa-dashboard"></i> <span translate="">Admin</span></a></li>
                <li class="active"><i class="fa fa-edit"></i> <span translate="">Aws configuration</span></li>
            </ol>
        </section>

        <section class="content">
            <form class="form-horizontal" name="awsForm" ng-submit="mainCtrl.save()">
                <div class="box box-{{awsForm.$valid && 'success' || 'danger'}}">
                    <div class="box-body">
                        <p class="help-block">
                            <span translate="">You can use Amazon's web services to host your website, send out emails, save user uploads, add a CDN, etc.</span>
                            <span translate="">To allow access to these resources, please create the required authorization keys in your Amazon account (via IAM) and paste the credentials here.</span>
                            <a href="" google-search="what is IAM"><span translate="">Learn more</span></a>
                        </p>

                        <div class="list-group-item list-group-item-bar" ng-repeat="(key, service) in services">
                            <div class="row">
                                <div class="col-xs-9">
                                    <div class="list-group-item-heading checkbox">
                                        <label tooltip="Enable service">
                                            <input type="checkbox" ng-model="settings.services[key].enabled" ng-change="mainCtrl.check(key, service, settings.services[key])">
                                            <span class="hidden-xs hidden-sm">Amazon</span> <b>{{service.name}}</b>
                                            <span class="muted hidden-xs hidden-sm">({{service.usage}})</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-xs-3">
                                    <button type="button" class="btn btn-flat btn-default btn-sm pull-right" ng-click="mainCtrl.configure(key, service)">
                                        <i class="fa fa-cog"></i> <span translate="">Configure</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="box-footer with-border">
                        <div class="pull-center">
                            <button type="submit" class="btn btn-flat btn-primary"><i class="fa fa-check"></i> <span translate="">Save All Settings</span></button>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </div>

    <script type="text/ng-template" id="/aws-configure.html">
        <div class="box">
            <div class="box-header with-border">
                <b class="pull-left"><span translate="">Configure Amazon</span> {{service.name}}</b>
                <a class="pull-right close-button" href=""><i class="fa fa-times"></i></a>
            </div>

            <form class="form-horizontal" name="serviceForm" ng-init="aws = settings.services; aws[key] = aws[key] || {}">
                <div class="box-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="key"><span translate="">Key:</span></label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control auto-focus" id="key" placeholder="Enter Key" ng-model="aws[key].key" ng-required="true">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="secret"><span translate="">Secret:</span></label>
                        <div class="col-sm-10">
                            <input type="password" class="form-control" id="secret" placeholder="Enter Secret" ng-model="aws[key].secret" ng-required="true">
                        </div>
                    </div>

                </div>

                <div class="box-footer with-border">
                    <button type="button" class="btn btn-flat btn-transparent btn-sm pull-left" ng-show="serviceForm.$valid" ng-click="mainCtrl.copy(aws[key])" tooltip="Use these credentials for all Amazon services">
                        <i class="fa fa-copy"></i> <span translate="">Copy to all services</span>
                    </button>
                    <button type="submit" class="btn btn-flat btn-primary pull-right close-button" ng-disabled="!serviceForm.$valid" ng-click="mainCtrl.alert()">
                        <span translate>Update</span> <i class="fa fa-fw fa-angle-right"></i>
                    </button>
                </div>
            </form>
        </div>
    </script>
</div>
