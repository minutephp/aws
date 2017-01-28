/// <reference path="../../../../../../../../public/static/bower_components/minute/_all.d.ts" />
var Admin;
(function (Admin) {
    var CdnConfigController = (function () {
        function CdnConfigController($scope, $minute, $ui, $timeout, gettext, gettextCatalog) {
            var _this = this;
            this.$scope = $scope;
            this.$minute = $minute;
            this.$ui = $ui;
            this.$timeout = $timeout;
            this.gettext = gettext;
            this.gettextCatalog = gettextCatalog;
            this.save = function () {
                _this.$scope.config.save(_this.gettext('Settings saved successfully'), false).catch(function (e) { return _this.$ui.toast(e.error.data, 'error'); });
            };
            gettextCatalog.setCurrentLanguage($scope.session.lang || 'en');
            $scope.data = { processors: [], tabs: {} };
            if ($scope.config = $scope.configs[0]) {
                $scope.settings = $scope.config.attr('data_json');
                $scope.services = angular.isObject($scope.settings.services) ? $scope.settings.services : {};
            }
        }
        return CdnConfigController;
    }());
    Admin.CdnConfigController = CdnConfigController;
    angular.module('cdnConfigApp', ['MinuteFramework', 'AdminApp', 'gettext'])
        .controller('cdnConfigController', ['$scope', '$minute', '$ui', '$timeout', 'gettext', 'gettextCatalog', CdnConfigController]);
})(Admin || (Admin = {}));
