<div class="content-wrapper ng-cloak" ng-app="cdnConfigApp" ng-controller="cdnConfigController as mainCtrl" ng-init="init()">
    <div class="admin-content">
        <section class="content-header">
            <h1>
                <span translate="">Upload and CDN settings</span>
            </h1>

            <ol class="breadcrumb">
                <li><a href="" ng-href="/admin"><i class="fa fa-dashboard"></i> <span translate="">Admin</span></a></li>
                <li><a href="" ng-href="/admin/aws/setup"><i class="fa fa-amazon"></i> <span translate="">AWS</span></a></li>
                <li class="active"><i class="fa fa-cog"></i> <span translate="">CDN</span></li>
            </ol>
        </section>

        <ng-switch on="!!services.s3.key && !!services.cloudfront.key">
            <section class="content padded" ng-switch-when="false">
                <div class="alert alert-warning alert-dismissible" role="alert">
                    <p translate="" ng-show="!services.s3.key">Please configure Amazon S3 access for user uploads.</p>

                    <p translate="" ng-show="!services.cloudfront.key">Please configure Amazon Cloudfront access for CDN.</p>

                    <br>

                    <a class="btn btn-primary" ng-href="/admin/aws/setup"><span translate="">Setup Amazon IAM access</span> <i class="fa fa-angle-right"></i></a>
                </div>
            </section>

            <section class="content" ng-switch-when="true">
                <form class="form-horizontal" name="cdnForm" ng-submit="mainCtrl.save()">
                    <div class="box box-{{cdnForm.$valid && 'success' || 'danger'}}">
                        <div class="box-header with-border">
                            <span translate="">Configure User Uploads and CDN</span>
                        </div>

                        <div class="box-body">
                            <div class="form-group" ng-init="settings.uploads.upload_bucket = settings.uploads.upload_bucket || ('www.' + session.site.domain)">
                                <label class="col-sm-3 control-label" for="upload_bucket"><span translate="">S3 upload bucket:</span></label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="upload_bucket" placeholder="Enter Upload bucket" ng-model="settings.uploads.upload_bucket" ng-required="true">
                                </div>
                            </div>

                            <div class="form-group" ng-init="settings.uploads.anonymous_uploads = settings.uploads.anonymous_uploads || true">
                                <label class="col-sm-3 control-label"><span translate="">Who can upload:</span></label>
                                <div class="col-sm-9">
                                    <label class="radio-inline">
                                        <input type="radio" ng-model="settings.uploads.anonymous_uploads" ng-value="false"> <span translate="">Member's only (login is required)</span>
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" ng-model="settings.uploads.anonymous_uploads" ng-value="true"> <span translate="">Anyone (guests can upload too)</span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label"><span translate="">Enable CDN for:</span></label>
                                <div class="col-sm-9">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" ng-model="settings.uploads.cloudfront_enabled"> <span translate="">User uploads</span>
                                    </label>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" ng-model="settings.static.cloudfront_enabled"> <span translate="">Static website assets</span>
                                    </label>

                                    <p class="help-block"><span translate="">(using CDN can drastically reduces load times)</span></p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label"><span translate="">SSL settings:</span></label>
                                <div class="col-sm-9">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" ng-model="settings.uploads.https_only">
                                            <span translate="">Serve uploads and assets using https:// only (SSL)</span>
                                            <p class="help-block">
                                                <span translate="">Requires *.{{session.site.domain}} to be added in Amazon certificate manager</span>
                                                - <a href="" google-search="amazon certificate manager"><span translate="">learn more</span></a>
                                            </p>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group" ng-if="settings.uploads.cloudfront_enabled" ng-init="settings.uploads.cdn_host = settings.uploads.cdn_host || ('uploads.' + session.site.domain)">
                                <label class="col-sm-3 control-label" for="upload_cdn"><span translate="">Host for uploads:</span></label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="upload_cdn" placeholder="Enter Cloudfront host for uploads" ng-model="settings.uploads.cdn_host"
                                           ng-required="true">
                                    <p class="help-block" ng-show="!!settings.uploads.cdn_cname"><span translate="">Add this CNAME entry to your DNS: {{settings.uploads.cdn_host}} =>
                                            {{settings.uploads.cdn_cname}}</span></p>
                                </div>
                            </div>

                            <div class="form-group" ng-if="settings.static.cloudfront_enabled" ng-init="settings.static.cdn_host = settings.static.cdn_host || ('static.' + session.site.domain)">
                                <label class="col-sm-3 control-label" for="upload_cdn"><span translate="">Host for static assets:</span></label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="upload_cdn" placeholder="Enter Cloudfront host for static assets" ng-model="settings.static.cdn_host"
                                           ng-required="true">
                                    <p class="help-block" ng-show="!!settings.static.cdn_cname"><span translate="">Add this CNAME entry to your DNS: {{settings.static.cdn_host}} =>
                                            {{settings.static.cdn_cname}}</span></p>
                                </div>
                            </div>


                        </div>

                        <div class="box-footer with-border">
                            <div class="form-group">
                                <div class="col-sm-offset-3 col-sm-9">
                                    <button type="submit" class="btn btn-flat btn-primary">
                                        <span translate="">Update settings</span>
                                        <i class="fa fa-fw fa-angle-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </section>
        </ng-switch>
    </div>
</div>
