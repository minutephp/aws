/// <reference path="../../../../../../../../public/static/bower_components/minute/_all.d.ts" />

module Admin {
    export class DeployConfigController {
        constructor(public $scope: any, public $minute: any, public $ui: any, public $timeout: ng.ITimeoutService,
                    public gettext: angular.gettext.gettextFunction, public gettextCatalog: angular.gettext.gettextCatalog) {

            gettextCatalog.setCurrentLanguage($scope.session.lang || 'en');

            $scope.data = {
                instances: ["t2.micro", "t2.small", "t2.medium", "t2.large", "m3.medium", "m3.large", "m3.xlarge", "m3.2xlarge", "c3.large", "c3.xlarge", "c3.2xlarge", "c3.4xlarge", "c3.8xlarge",
                    "t1.micro", "t2.nano", "m1.small", "m1.medium", "m1.large", "m1.xlarge", "c1.medium", "c1.xlarge", "c4.large", "c4.xlarge", "c4.2xlarge", "c4.4xlarge", "c4.8xlarge", "m2.xlarge",
                    "m2.2xlarge", "m2.4xlarge", "m4.large", "m4.xlarge", "m4.2xlarge", "m4.4xlarge", "m4.10xlarge", "cc1.4xlarge", "cc2.8xlarge", "hi1.4xlarge", "hs1.8xlarge", "cr1.8xlarge", "g2.2xlarge",
                    "g2.8xlarge", "i2.xlarge", "i2.2xlarge", "i2.4xlarge", "i2.8xlarge", "r3.large", "r3.xlarge", "r3.2xlarge", "r3.4xlarge", "r3.8xlarge"]
            };

            if ($scope.config = $scope.configs[0]) {
                let name = $scope.session.site.site_name.replace(/[^a-zA-Z0-9\-]/, '-');
                let defaults = {app_name: name, rds: {size: 5, instance: 'db.t2.micro'}, web: {instance: 't2.micro'}, worker: {instance: 't2.micro'}, tweaks: true};
                $scope.settings = $scope.config.attr('data_json');
                $scope.services = angular.isObject($scope.settings.services) ? $scope.settings.services : {};
                $scope.settings.deployment = angular.isObject($scope.settings.deployment) ? $scope.settings.deployment : defaults;
                $scope.settings.deployment.secret = $scope.settings.deployment.secret || Minute.Utils.randomString();
            }
        };

        removeRds = () => {
            this.$ui.alert(this.gettext('You will have to manually terminate and remove the current RDS instance (via Amazon) before starting deployment'))
                .then(() => this.$timeout(() => this.$scope.settings.deployment.instances.rds = null));
        };

        resetKey = () => {
            this.$ui.confirm().then(() => {
                this.$scope.settings.deployment.secret = Minute.Utils.randomString();
                this.$scope.config.save(this.gettext('Secret key updated successfully.'))
            });
        };

        dryRun = () => {
            this.save('&dry_run=true');
        };

        save = (params = '') => {
            this.$scope.data.loading = true;
            this.$scope.config.save(this.gettext('Deployment settings saved successfully.')).then(
                () => {
                    let deployer = $('#deployer');
                    deployer.attr('action', '/_aws/deploy?key=' + this.$scope.settings.deployment.secret + params);
                    deployer.submit();
                },
                () => this.$scope.data.loading = false
            );
        };
    }

    angular.module('deployConfigApp', ['MinuteFramework', 'AdminApp', 'gettext'])
        .controller('deployConfigController', ['$scope', '$minute', '$ui', '$timeout', 'gettext', 'gettextCatalog', DeployConfigController]);
}
