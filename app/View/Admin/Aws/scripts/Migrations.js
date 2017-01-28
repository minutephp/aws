/// <reference path="../../../../../../../../public/static/bower_components/minute/_all.d.ts" />
var App;
(function (App) {
    var MigrationListController = (function () {
        function MigrationListController($scope, $minute, $ui, $timeout, gettext, gettextCatalog) {
            var _this = this;
            this.$scope = $scope;
            this.$minute = $minute;
            this.$ui = $ui;
            this.$timeout = $timeout;
            this.gettext = gettext;
            this.gettextCatalog = gettextCatalog;
            this.run = function () {
                _this.$ui.confirm(_this.gettext('Have you made a backup of your database? Press OK to run migrations'))
                    .then(function () {
                    $('#runner').submit();
                    _this.$timeout(function () { return _this.$scope.data.running = true; });
                });
            };
            gettextCatalog.setCurrentLanguage($scope.session.lang || 'en');
            $scope.data = { phinx: { pending: false } };
        }
        return MigrationListController;
    }());
    App.MigrationListController = MigrationListController;
    angular.module('migrationListApp', ['MinuteFramework', 'AdminApp', 'gettext'])
        .controller('migrationListController', ['$scope', '$minute', '$ui', '$timeout', 'gettext', 'gettextCatalog', MigrationListController]);
})(App || (App = {}));
