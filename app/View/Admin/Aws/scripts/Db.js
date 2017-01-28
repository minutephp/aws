/// <reference path="../../../../../../../../public/static/bower_components/minute/_all.d.ts" />
var Admin;
(function (Admin) {
    var DatabaseConfigController = (function () {
        function DatabaseConfigController($scope, $minute, $ui, $timeout, gettext, gettextCatalog) {
            var _this = this;
            this.$scope = $scope;
            this.$minute = $minute;
            this.$ui = $ui;
            this.$timeout = $timeout;
            this.gettext = gettext;
            this.gettextCatalog = gettextCatalog;
            this.mirror = function (tweak) {
                _this.$ui.confirm(_this.gettext("This will erase your current RDS instance and replace it contents of your local database.<br><br>Are you sure?"))
                    .then(function () {
                    _this.$scope.data.loading = true;
                    _this.$scope.data.scroll = true;
                    $('#databaseForm').submit();
                    _this.$timeout(_this.scroll);
                });
            };
            this.scroll = function () {
                var $contents = $('#frameProgress').contents();
                if (_this.$scope.data.scroll) {
                    $contents.scrollTop($contents.height());
                }
                if (_this.$scope.data.loading) {
                    _this.$timeout(_this.scroll, 100);
                }
            };
            gettextCatalog.setCurrentLanguage($scope.session.lang || 'en');
            $scope.data = { loading: false, scroll: true, tweak: true };
            $scope.config = $scope.configs[0] || $scope.configs.create().attr('type', 'database').attr('data_json', {});
            $scope.settings = $scope.config.attr('data_json');
        }
        return DatabaseConfigController;
    }());
    Admin.DatabaseConfigController = DatabaseConfigController;
    angular.module('databaseConfigApp', ['MinuteFramework', 'AdminApp', 'gettext'])
        .controller('databaseConfigController', ['$scope', '$minute', '$ui', '$timeout', 'gettext', 'gettextCatalog', DatabaseConfigController]);
})(Admin || (Admin = {}));
