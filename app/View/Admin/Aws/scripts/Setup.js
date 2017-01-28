/// <reference path="../../../../../../../../public/static/bower_components/minute/_all.d.ts" />
var Admin;
(function (Admin) {
    var AwsEditController = (function () {
        function AwsEditController($scope, $minute, $ui, $timeout, gettext, gettextCatalog) {
            var _this = this;
            this.$scope = $scope;
            this.$minute = $minute;
            this.$ui = $ui;
            this.$timeout = $timeout;
            this.gettext = gettext;
            this.gettextCatalog = gettextCatalog;
            this.configure = function (key, service) {
                _this.$ui.popupUrl('/aws-configure.html', false, _this.$scope, { ctrl: _this, key: key, service: service }).then(function () {
                    var config = _this.$scope.settings.services[key];
                    config.enabled = config.enabled && !!config.key && !!config.secret;
                    _this.$timeout();
                });
            };
            this.check = function (key, service, config) {
                if (config.enabled && (!config.key || !config.secret)) {
                    _this.configure(key, service);
                }
            };
            this.copy = function (obj) {
                console.log("obj: ", obj);
                var data = _this.$scope.settings;
                angular.forEach(_this.$scope.services, function (v, k) {
                    data.services[k] = data.services[k] || {};
                    data.services[k]['key'] = obj.key;
                    data.services[k]['secret'] = obj.secret;
                    data.services[k]['enabled'] = true;
                });
                _this.$ui.toast(_this.gettext('Credentials copied to all Amazon services!'), 'success');
            };
            this.alert = function () {
                _this.$ui.toast(_this.gettext('Remember to click "Save all settings" to commit your changes!'));
            };
            this.save = function () {
                _this.$scope.config.save(_this.gettext('AWS settings updated successfully'));
            };
            gettextCatalog.setCurrentLanguage($scope.session.lang || 'en');
            $scope.config = $scope.configs[0] || $scope.configs.create().attr('type', 'aws').attr('data_json', {});
            $scope.settings = $scope.config.attr('data_json');
            $scope.settings.services = angular.isObject($scope.settings.services) ? $scope.settings.services : {};
            $scope.services = {
                's3': { name: 'S3', usage: gettext('for user uploads') },
                'ec2': { name: 'EC2', usage: gettext('for site hosting') },
                'ses': { name: 'SES', usage: gettext('for sending emails') },
                'cloudfront': { name: 'Cloudfront', usage: gettext('for faster loading') },
                'beanstalk': { name: 'Beanstalk', usage: gettext('for auto-scaling') },
                'rds': { name: 'RDS', usage: gettext('for scalable databases') },
                'sqs': { name: 'SQS', usage: gettext('for running cron jobs') }
            };
        }
        return AwsEditController;
    }());
    Admin.AwsEditController = AwsEditController;
    angular.module('awsEditApp', ['MinuteFramework', 'AdminApp', 'gettext'])
        .controller('awsEditController', ['$scope', '$minute', '$ui', '$timeout', 'gettext', 'gettextCatalog', AwsEditController]);
})(Admin || (Admin = {}));
