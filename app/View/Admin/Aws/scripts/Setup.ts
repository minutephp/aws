/// <reference path="../../../../../../../../public/static/bower_components/minute/_all.d.ts" />

module Admin {
    export class AwsEditController {
        constructor(public $scope: any, public $minute: any, public $ui: any, public $timeout: ng.ITimeoutService,
                    public gettext: angular.gettext.gettextFunction, public gettextCatalog: angular.gettext.gettextCatalog) {

            gettextCatalog.setCurrentLanguage($scope.session.lang || 'en');

            $scope.config = $scope.configs[0] || $scope.configs.create().attr('type', 'aws').attr('data_json', {});
            $scope.settings = $scope.config.attr('data_json');
            $scope.settings.services = angular.isObject($scope.settings.services) ? $scope.settings.services : {};

            $scope.services = {
                's3': {name: 'S3', usage: gettext('for user uploads')},
                'ec2': {name: 'EC2', usage: gettext('for site hosting')},
                'ses': {name: 'SES', usage: gettext('for sending emails')},
                'cloudfront': {name: 'Cloudfront', usage: gettext('for faster loading')},
                'beanstalk': {name: 'Beanstalk', usage: gettext('for auto-scaling')},
                'rds': {name: 'RDS', usage: gettext('for scalable databases')},
                'sqs': {name: 'SQS', usage: gettext('for running cron jobs')},
            };
        }


        configure = (key, service) => {
            this.$ui.popupUrl('/aws-configure.html', false, this.$scope, {ctrl: this, key: key, service: service}).then(() => {
                let config = this.$scope.settings.services[key];
                config.enabled = config.enabled && !!config.key && !!config.secret;
                this.$timeout();
            });
        };

        check = (key, service, config) => {
            if (config.enabled && (!config.key || !config.secret)) {
                this.configure(key, service);
            }
        };

        copy = (obj) => {
            console.log("obj: ", obj);
            let data = this.$scope.settings;

            angular.forEach(this.$scope.services, function (v, k) {
                data.services[k] = data.services[k] || {};
                data.services[k]['key'] = obj.key;
                data.services[k]['secret'] = obj.secret;
                data.services[k]['enabled'] = true;
            });

            this.$ui.toast(this.gettext('Credentials copied to all Amazon services!'), 'success');
        };

        alert = () => {
            this.$ui.toast(this.gettext('Remember to click "Save all settings" to commit your changes!'));
        };

        save = () => {
            this.$scope.config.save(this.gettext('AWS settings updated successfully'));
        };
    }

    angular.module('awsEditApp', ['MinuteFramework', 'AdminApp', 'gettext'])
        .controller('awsEditController', ['$scope', '$minute', '$ui', '$timeout', 'gettext', 'gettextCatalog', AwsEditController]);
}
