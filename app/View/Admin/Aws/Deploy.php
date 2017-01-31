<div class="content-wrapper ng-cloak" ng-app="deployConfigApp" ng-controller="deployConfigController as mainCtrl" ng-init="init()">
    <div class="admin-content">
        <section class="content-header">
            <h1>
                <span translate="">Automatic website deployment</span>
            </h1>

            <ol class="breadcrumb">
                <li><a href="" ng-href="/admin"><i class="fa fa-dashboard"></i> <span translate="">Admin</span></a></li>
                <li class="active"><i class="fa fa-amazon"></i> <span translate="">Deploy site</span></li>
            </ol>
        </section>

        <section class="content" ng-switch="" on="!!services.beanstalk.key && !!services.ec2.key && !!services.rds.key">
            <div class="padded" ng-switch-when="false">
                <div class="alert alert-warning alert-dismissible" role="alert">
                    <p translate="" ng-show="!services.beanstalk.key">Please configure Amazon Elastic Beanstalk access for automatic deployment.</p>

                    <p translate="" ng-show="!services.ec2.key">Please configure Amazon EC2 access for automatic deployment.</p>

                    <p translate="" ng-show="!services.rds.key">Please configure Amazon RDS access for automatic deployment.</p>

                    <br>

                    <a class="btn btn-primary" ng-href="/admin/aws/setup"><span translate="">Setup Amazon IAM access</span> <i class="fa fa-angle-right"></i></a>
                </div>
            </div>

            <div ng-switch-when="true">
                <minute-event name="import.git.status" as="data.git"></minute-event>

                <div class="alert alert-warning alert-dismissible" role="alert" ng-if="data.git.status === 'uncommitted'">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <span translate="">Looks like you haven't committed changes to your local Git repo. Please make sure to commit and push changes before you begin deployment!</span>
                </div>

                <form class="form-horizontal" name="configForm" ng-submit="mainCtrl.save()">
                    <div class="box box-{{deployForm.$valid && 'success' || 'danger'}}">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                                <i class="fa fa-cloud-upload"></i> <span translate="" class="hidden-xs">Deploy website to AWS</span>
                            </h3>
                        </div>

                        <div class="box-body">
                            <div class="form-group">
                                <label class="col-sm-3 control-label" for="app_name"><span translate="">Application name:</span></label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="app_name" placeholder="Enter Application name" ng-model="settings.deployment.app_name" ng-required="true"
                                           pattern="^[a-zA-Z0-9][a-zA-Z0-9\-]+[a-zA-Z0-9]$" minlength="3" maxlength="20">
                                    <p class="help-block"><span translate="">(alphanumeric only)</span></p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label" for="git_url"><span translate="">Repository Url:</span></label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" placeholder="Git url of repository on GitHub or BitBucket | git@bitbucket.org:username/repo.git"
                                           ng-model="settings.deployment.repo_url" ng-required="true" pattern="^git@.*">
                                    <p class="help-block" translate="">(please make sure to use the SSH version, i.e. git@bitbucket.org:username/repo.git)</p>
                                </div>
                            </div>

                            <div class="form-group" ng-init="settings.deployment.repo_type = settings.deployment.repo_type || 'public'">
                                <label class="col-sm-3 control-label"><span translate="">Repository access:</span></label>
                                <div class="col-sm-9">
                                    <label class="radio-inline">
                                        <input type="radio" ng-model="settings.deployment.repo_type" ng-value="'public'"> Public (anyone can access)
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" ng-model="settings.deployment.repo_type" ng-value="'private'"> Private (using SSH)
                                    </label>
                                </div>
                            </div>

                            <div class="form-group" ng-if="settings.deployment.repo_type === 'private'" ng-init="data.hideKey = !!settings.deployment.repo_ppk">
                                <label class="col-sm-3 control-label" for="deployment_key"><span translate="">Private key:</span></label>
                                <ng-switch on="!!data.hideKey">
                                    <div ng-switch-when="false">
                                        <div class="col-sm-9">
                                            <textarea rows="3" class="form-control" placeholder="Paste Deployment private key (OpenSSH format only)" ng-model="settings.deployment.repo_ppk"
                                                      ng-required="true"></textarea>
                                        </div>
                                        <div class="col-sm-9 col-sm-offset-3">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" ng-model="data.check2" ng-required="true">
                                                    <span translate="">My private key is in OpenSSH format (not ppk)</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-9 col-sm-offset-3">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" ng-model="data.check3" ng-required="true">
                                                    <span translate="">I have added the corresponding public key as Deployment key in GitHub or BitBucket</span>
                                                    - <a href="https://developer.github.com/guides/managing-deploy-keys/#deploy-keys" target="_blank"><span translate="">learn more</span></a>
                                                </label>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="col-sm-9" ng-switch-when="true">
                                        <p class="help-block">
                                            <button type="button" class="btn btn-flat btn-xs btn-default" ng-click="data.hideKey = false">
                                                <i class="fa fa-edit"></i> <span translate="">Change..</span>
                                            </button>
                                        </p>
                                    </div>
                                </ng-switch>
                            </div>

                            <div class="form-group" ng-if="!!settings.deployment.instances.rds">
                                <label class="col-sm-3 control-label"><span translate="">RDS database:</span></label>
                                <div class="col-sm-9">
                                    <p class="help-block">
                                        <button type="button" class="btn btn-flat btn-default btn-xs" ng-click="mainCtrl.removeRds()">
                                            <i class="fa fa-trash"></i> <span translate="">Remove RDS instance</span>
                                        </button>
                                    </p>
                                </div>
                            </div>

                            <div class="form-group" ng-if="!settings.deployment.instances.rds">
                                <label class="col-sm-3 control-label" for="rds"><span translate="">RDS settings:</span></label>
                                <div class="col-sm-4">
                                    <select class="form-control" ng-model="settings.deployment.rds.instance" ng-required="true" title="instance type">
                                        <option ng-repeat="instance in data.instances">db.{{instance}}</option>
                                    </select>
                                    <p class="help-block"><span translate="">(instance type of database)</span></p>
                                </div>
                                <div class="col-sm-5">
                                    <div class="input-group">
                                        <input type="number" min="5" max="1024" class="form-control" id="rds" placeholder="Enter database size" ng-model="settings.deployment.rds.size"
                                               ng-required="true">
                                        <div class="input-group-addon">GB</div>
                                    </div>

                                    <p class="help-block"><span translate="">(size of your database in GB)</span></p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label" for="rds"><span translate="">Environment settings:</span></label>
                                <div class="col-sm-3">
                                    <select class="form-control" ng-model="settings.deployment.web.instance" ng-required="true" title="instance type">
                                        <option ng-repeat="instance in data.instances">{{instance}}</option>
                                    </select>
                                    <p class="help-block"><span translate="">(instance type of web server)</span></p>
                                </div>
                                <div class="col-sm-3">
                                    <select class="form-control" ng-model="settings.deployment.worker.instance" ng-required="false" title="instance type">
                                        <option value="">Not required</option>
                                        <option ng-repeat="instance in data.instances">{{instance}}</option>
                                    </select>
                                    <p class="help-block"><span translate="">(instance type of cron server)</span></p>
                                </div>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control" ng-required="false" ng-model="settings.deployment.keypair" placeholder="EC2 SSH key-pair for debugging">
                                    <p class="help-block"><span translate="">(EC2 SSH key-pair - optional)</span></p>
                                </div>
                            </div>

                            <div class="form-group" ng-init="settings.deployment.docker_image = settings.deployment.docker_image || 'ubuntu:latest';">
                                <label class="col-sm-3 control-label" for="docker"><span translate="">Docker image:</span></label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control" id="docker" placeholder="Enter Docker image" ng-model="settings.deployment.docker_image" ng-required="true">
                                </div>
                            </div>

                            <div class="form-group" ng-init="settings.deployment.singleInstance = false">
                                <label class="col-sm-3 control-label"><span translate="">Capacity:</span></label>
                                <div class="col-sm-9">
                                    <label class="radio-inline">
                                        <input type="radio" ng-model="settings.deployment.singleInstance" ng-value="false"> <span translate="">High traffic (Load balancer, auto-scaling, https support)</span>
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" ng-model="settings.deployment.singleInstance" ng-value="true"> <span translate="">Low traffic (single instance - cheaper)</span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label" for="rds"><span translate="">Website settings:</span></label>

                                <div class="col-sm-9">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" ng-model="settings.deployment.https_only">
                                            <span translate="">Redirect all non-https traffic to https.</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-sm-9 col-sm-offset-3" ng-if="!!settings.static.cdn_cname">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" ng-model="settings.deployment.cdn_enabled">
                                            <span translate="">Serve all content in /static folder using Amazon Cloudfront (CDN) for faster delivery</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-sm-9 col-sm-offset-3" ng-show="settings.deployment.cdn_enabled">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" ng-model="settings.deployment.cdn_reload">
                                            <span translate="">Invalidate CDN and reload all /static assets after deployment (may incur <a href="" google-search="cloudfront invalidation cost">costs</a>)</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-sm-9 col-sm-offset-3">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" ng-model="settings.deployment.tweaks">
                                            <span translate="">Apply common website tweaks and settings after deployment (for better performance)</span>
                                        </label>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <div class="box-footer with-border">
                            <div class="form-group">
                                <div class="col-sm-9 col-sm-offset-3" ng-if="settings.deployment.https_only">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" ng-model="data.check4" ng-required="true">
                                            <span translate="">I have added "{{session.site.domain}}" and "*.{{session.site.domain}}" to Amazon's certificate manager</span> -
                                            <a href="" google-search="amazon certificate manager"><span translate="">learn more</span></a>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-sm-offset-3 col-sm-9">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" ng-model="data.check1" ng-required="true"> <span translate="">I have committed all changes to remote repo</span>
                                        </label>
                                    </div>
                                </div>

                                <br>&nbsp;

                                <div class="col-sm-offset-3 col-sm-9">
                                    <div ng-show="!!data.loading">
                                        <i class="fa fa-spinner fa-spin"></i> <span translate="">Please wait while your website is being deployed (this may take a few minutes).</span>
                                    </div>
                                    <div class="btn-group" ng-show="!data.loading">
                                        <button type="submit" class="btn btn-flat btn-primary"><span translate="">Begin deployment</span> <i class="fa fa-fw fa-angle-right"></i></button>
                                        <button type="button" class="btn btn-flat btn-primary dropdown-toggle" data-toggle="dropdown" ng-disabled="!configForm.$valid"><span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a href="" ng-click="mainCtrl.dryRun()">Do a dry run</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <form name="deployForm" id="deployer" method="POST"></form>
            </div>
        </section>
    </div>
</div>
