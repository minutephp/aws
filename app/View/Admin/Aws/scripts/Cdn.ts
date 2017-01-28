/// <reference path="../../../../../../../../public/static/bower_components/minute/_all.d.ts" />

module Admin {
    export class CdnConfigController {
        constructor(public $scope: any, public $minute: any, public $ui: any, public $timeout: ng.ITimeoutService,
                    public gettext: angular.gettext.gettextFunction, public gettextCatalog: angular.gettext.gettextCatalog) {

            gettextCatalog.setCurrentLanguage($scope.session.lang || 'en');

            $scope.data = {processors: [], tabs: {}};

            if ($scope.config = $scope.configs[0]) {
                $scope.settings = $scope.config.attr('data_json');
                $scope.services = angular.isObject($scope.settings.services) ? $scope.settings.services : {};
            }
        }

        save = () => {
            this.$scope.config.save(this.gettext('Settings saved successfully'), false).catch((e) => this.$ui.toast(e.error.data, 'error'));
        };
    }

    angular.module('cdnConfigApp', ['MinuteFramework', 'AdminApp', 'gettext'])
        .controller('cdnConfigController', ['$scope', '$minute', '$ui', '$timeout', 'gettext', 'gettextCatalog', CdnConfigController]);
}
