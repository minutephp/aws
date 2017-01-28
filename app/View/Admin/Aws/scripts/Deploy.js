/// <reference path="../../../../../../../../public/static/bower_components/minute/_all.d.ts" />
var Admin;
(function (Admin) {
    var DeployConfigController = (function () {
        function DeployConfigController($scope, $minute, $ui, $timeout, gettext, gettextCatalog) {
            var _this = this;
            this.$scope = $scope;
            this.$minute = $minute;
            this.$ui = $ui;
            this.$timeout = $timeout;
            this.gettext = gettext;
            this.gettextCatalog = gettextCatalog;
            this.removeRds = function () {
                _this.$ui.alert(_this.gettext('You will have to manually terminate and remove the current RDS instance (via Amazon) before starting deployment'))
                    .then(function () { return _this.$timeout(function () { return _this.$scope.settings.deployment.instances.rds = null; }); });
            };
            this.resetKey = function () {
                _this.$ui.confirm().then(function () {
                    _this.$scope.settings.deployment.secret = Minute.Utils.randomString();
                    _this.$scope.config.save(_this.gettext('Secret key updated successfully.'));
                });
            };
            this.dryRun = function () {
                _this.save('&dry_run=true');
            };
            this.save = function (params) {
                if (params === void 0) { params = ''; }
                _this.$scope.data.loading = true;
                _this.$scope.config.save(_this.gettext('Deployment settings saved successfully.')).then(function () {
                    var deployer = $('#deployer');
                    deployer.attr('action', '/_aws/deploy?key=' + _this.$scope.settings.deployment.secret + params);
                    deployer.submit();
                }, function () { return _this.$scope.data.loading = false; });
            };
            gettextCatalog.setCurrentLanguage($scope.session.lang || 'en');
            $scope.data = {
                instances: ["t2.micro", "t2.small", "t2.medium", "t2.large", "m3.medium", "m3.large", "m3.xlarge", "m3.2xlarge", "c3.large", "c3.xlarge", "c3.2xlarge", "c3.4xlarge", "c3.8xlarge",
                    "t1.micro", "t2.nano", "m1.small", "m1.medium", "m1.large", "m1.xlarge", "c1.medium", "c1.xlarge", "c4.large", "c4.xlarge", "c4.2xlarge", "c4.4xlarge", "c4.8xlarge", "m2.xlarge",
                    "m2.2xlarge", "m2.4xlarge", "m4.large", "m4.xlarge", "m4.2xlarge", "m4.4xlarge", "m4.10xlarge", "cc1.4xlarge", "cc2.8xlarge", "hi1.4xlarge", "hs1.8xlarge", "cr1.8xlarge", "g2.2xlarge",
                    "g2.8xlarge", "i2.xlarge", "i2.2xlarge", "i2.4xlarge", "i2.8xlarge", "r3.large", "r3.xlarge", "r3.2xlarge", "r3.4xlarge", "r3.8xlarge"]
            };
            if ($scope.config = $scope.configs[0]) {
                var name_1 = $scope.session.site.site_name.replace(/[^a-zA-Z0-9\-]/, '-');
                var defaults = { app_name: name_1, rds: { size: 5, instance: 'db.t2.micro' }, web: { instance: 't2.micro' }, worker: { instance: 't2.micro' }, tweaks: true };
                $scope.settings = $scope.config.attr('data_json');
                $scope.services = angular.isObject($scope.settings.services) ? $scope.settings.services : {};
                $scope.settings.deployment = angular.isObject($scope.settings.deployment) ? $scope.settings.deployment : defaults;
                $scope.settings.deployment.secret = $scope.settings.deployment.secret || Minute.Utils.randomString();
            }
        }
        ;
        return DeployConfigController;
    }());
    Admin.DeployConfigController = DeployConfigController;
    angular.module('deployConfigApp', ['MinuteFramework', 'AdminApp', 'gettext'])
        .controller('deployConfigController', ['$scope', '$minute', '$ui', '$timeout', 'gettext', 'gettextCatalog', DeployConfigController]);
})(Admin || (Admin = {}));
